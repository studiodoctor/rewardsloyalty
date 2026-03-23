<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Agent API: Transaction operations for partners (POS/Shopify/WooCommerce).
 *
 * These are the most critical agent endpoints. They handle the money flow:
 * - purchase: Record a sale → award loyalty points
 * - redeem: Claim a reward → deduct points
 *
 * Business logic lives in TransactionService — this controller is an
 * adapter that translates agent API requests into service calls.
 *
 * "Earning is staff-initiated" invariant preserved: agent key performs
 * the action, optional staff_id delegates attribution.
 *
 * Mirror source: StaffController@addPurchase, StaffController@redeemReward
 * Service layer: TransactionService (injected, not duplicated)
 *
 * @see RewardLoyalty-100b-phase2-core-endpoints.md §2.4
 */

namespace App\Http\Controllers\Api\Agent\Partner;

use App\Http\Controllers\Api\Agent\BaseAgentController;
use App\Http\Controllers\Api\Agent\Concerns\EnforcesPartnerGates;
use App\Models\Card;
use App\Models\Reward;
use App\Models\Staff;
use App\Models\Transaction;
use App\Services\Card\TransactionService;
use App\Services\Member\MemberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AgentTransactionController extends BaseAgentController
{
    use EnforcesPartnerGates;

    public function __construct(
        private TransactionService $transactionService,
        private MemberService $memberService,
    ) {}

    /**
     * GET /api/agent/v1/partner/transactions
     * Scope: read | write:transactions
     *
     * List transactions for this partner, with optional filters.
     *
     * Filters:
     * - member_identifier: UUID, email, or unique_identifier
     * - card_id: Limit to a specific loyalty card
     * - event: Filter by event type (e.g., staff_credited_points_for_purchase)
     * - from/to: Date range (Y-m-d)
     */
    public function index(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read', 'write:transactions')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        $query = Transaction::where('created_by', $partner->id)
            ->orderBy('created_at', 'desc');

        // Filter by member
        if ($request->filled('member_identifier')) {
            $member = $this->resolveMember($request->input('member_identifier'));
            if (! $member) {
                return $this->jsonNotFound('Member');
            }
            $query->where('member_id', $member->id);
        }

        // Filter by card
        if ($request->filled('card_id')) {
            $card = Card::where('id', $request->input('card_id'))
                ->where('created_by', $partner->id)
                ->first();
            if (! $card) {
                return $this->jsonNotFound('Card');
            }
            $query->where('card_id', $card->id);
        }

        // Filter by event type
        if ($request->filled('event')) {
            $query->where('event', $request->input('event'));
        }

        // Date range
        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->input('from') . ' 00:00:00');
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->input('to') . ' 23:59:59');
        }

        $transactions = $query->paginate($this->getPerPage());

        // Serialize for agent API
        $items = $transactions->getCollection()->map(fn ($t) => [
            'id' => $t->id,
            'event' => $t->event,
            'points' => $t->points,
            'purchase_amount' => $t->purchase_amount,
            'currency' => $t->currency,
            'member_id' => $t->member_id,
            'card_id' => $t->card_id,
            'reward_id' => $t->reward_id,
            'staff_id' => $t->staff_id,
            'staff_name' => $t->staff_name,
            'note' => $t->note,
            'expires_at' => $t->expires_at?->toIso8601String(),
            'created_at' => $t->created_at?->toIso8601String(),
        ]);

        return $this->jsonSuccess([
            'data' => $items->toArray(),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * POST /api/agent/v1/partner/transactions/purchase
     * Scope: write:transactions
     *
     * Record a purchase → award loyalty points.
     *
     * Accepts flexible member identification (UUID, email, member_number,
     * unique_identifier) for maximum POS integration flexibility.
     */
    public function purchase(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:transactions')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        $validator = Validator::make($request->all(), [
            'card_id' => 'required|uuid',
            'member_identifier' => 'required|string',
            'purchase_amount' => 'required_without:points|numeric|min:0.01',
            'points' => 'nullable|integer|min:0',
            'staff_id' => 'nullable|uuid',
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray());
        }

        // 1. Resolve card (must belong to partner)
        $card = Card::where('id', $request->input('card_id'))
            ->where('created_by', $partner->id)
            ->first();

        if (! $card) {
            return $this->jsonNotFound('Card');
        }

        if (! $card->is_active) {
            return $this->jsonError(
                code: 'CARD_INACTIVE',
                message: 'This card is not currently active.',
                status: 422,
            );
        }

        // 2. Resolve member
        $member = $this->memberService->findActiveByIdentifier($request->input('member_identifier'));
        if (! $member) {
            return $this->jsonNotFound('Member');
        }

        // 3. Optional: validate staff_id belongs to partner
        $staff = null;
        $staffId = $request->input('staff_id');
        if ($staffId) {
            $staff = Staff::where('id', $staffId)
                ->where('created_by', $partner->id)
                ->first();
            if (! $staff) {
                return $this->jsonNotFound('Staff');
            }
        }

        // 4. Determine if this is a points-only or purchase-based transaction
        $purchaseAmount = $request->input('purchase_amount');
        $pointsOnly = $purchaseAmount === null && $request->filled('points');

        try {
            // Use the shared service — same business logic as StaffController
            // The service expects a staff member; for agent-initiated transactions
            // without a staff_id, we need to use the first staff member or create
            // a system-level approach. For now, we use a direct transaction approach.

            if ($staff) {
                $transaction = $this->transactionService->addPurchase(
                    member_identifier: $request->input('member_identifier'),
                    card_identifier: $card->unique_identifier,
                    staff: $staff,
                    purchase_amount: $pointsOnly ? null : (float) $purchaseAmount,
                    points: $request->filled('points') ? (float) $request->input('points') : null,
                    image: null,
                    note: $request->input('note'),
                    points_only: $pointsOnly,
                );
            } else {
                $transaction = $this->transactionService->addAgentPurchase(
                    member: $member,
                    card: $card,
                    purchase_amount: $pointsOnly ? null : (float) $purchaseAmount,
                    points: $request->filled('points') ? (int) $request->input('points') : null,
                    note: $request->input('note'),
                );
            }

            $newBalance = $card->getMemberBalance($member);

            return $this->jsonSuccess([
                'data' => [
                    'transaction_id' => $transaction->id,
                    'points_awarded' => $transaction->points,
                    'member_balance' => $newBalance,
                    'purchase_amount' => $purchaseAmount,
                    'card_id' => $card->id,
                    'member_id' => $member->id,
                ],
            ], 201);
        } catch (\Exception $e) {
            report($e);

            return $this->jsonError(
                code: 'TRANSACTION_FAILED',
                message: 'Unable to process this transaction. Please verify the input and try again.',
                status: 422,
                retryStrategy: 'fix_request',
            );
        }
    }

    /**
     * POST /api/agent/v1/partner/transactions/redeem
     * Scope: write:rewards
     *
     * Redeem a reward → deduct points from member balance.
     */
    public function redeem(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:rewards')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        $validator = Validator::make($request->all(), [
            'card_id' => 'required|uuid',
            'reward_id' => 'required|uuid',
            'member_identifier' => 'required|string',
            'staff_id' => 'nullable|uuid',
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray());
        }

        // 1. Resolve card
        $card = Card::where('id', $request->input('card_id'))
            ->where('created_by', $partner->id)
            ->first();

        if (! $card) {
            return $this->jsonNotFound('Card');
        }

        // 2. Resolve reward
        $reward = Reward::where('id', $request->input('reward_id'))
            ->where('created_by', $partner->id)
            ->first();

        if (! $reward) {
            return $this->jsonNotFound('Reward');
        }

        // 2b. Verify reward is linked to this card (prevent cross-card redemption)
        if (! $reward->cards()->where('cards.id', $card->id)->exists()) {
            return $this->jsonError(
                code: 'REWARD_NOT_LINKED',
                message: 'This reward is not available on this card.',
                status: 422,
                retryStrategy: 'fix_request',
            );
        }

        // 3. Resolve member
        $member = $this->memberService->findActiveByIdentifier($request->input('member_identifier'));
        if (! $member) {
            return $this->jsonNotFound('Member');
        }

        // 4. Check balance
        $balance = $card->getMemberBalance($member);
        if ($balance < $reward->points) {
            return $this->jsonError(
                code: 'INSUFFICIENT_POINTS',
                message: 'Member does not have enough points for this reward.',
                status: 422,
                retryStrategy: 'no_retry',
                details: [
                    'required' => $reward->points,
                    'available' => $balance,
                ],
            );
        }

        // 5. Optional staff delegation
        $staff = null;
        $staffId = $request->input('staff_id');
        if ($staffId) {
            $staff = Staff::where('id', $staffId)
                ->where('created_by', $partner->id)
                ->first();
            if (! $staff) {
                return $this->jsonNotFound('Staff');
            }
        }

        try {
            if ($staff) {
                $transaction = $this->transactionService->claimReward(
                    card_id: $card->id,
                    reward_id: $reward->id,
                    member_identifier: $member->id,
                    staff: $staff,
                    note: $request->input('note'),
                );

                if ($transaction === false) {
                    return $this->jsonError(
                        code: 'INSUFFICIENT_POINTS',
                        message: 'Insufficient points for this reward.',
                        status: 422,
                    );
                }
            } else {
                // Agent-initiated redemption without staff delegation:
                // Use systemClaimReward — same FIFO deduction + stats as staff-initiated,
                // but attributed to 'System' instead of a staff member.
                $transaction = $this->transactionService->systemClaimReward(
                    card: $card,
                    reward: $reward,
                    member: $member,
                    note: $request->input('note'),
                );

                if ($transaction === false) {
                    return $this->jsonError(
                        code: 'INSUFFICIENT_POINTS',
                        message: 'Insufficient points for this reward.',
                        status: 422,
                    );
                }
            }

            $newBalance = $card->getMemberBalance($member);

            return $this->jsonSuccess([
                'data' => [
                    'transaction_id' => $transaction->id,
                    'points_deducted' => $reward->points,
                    'member_balance' => $newBalance,
                    'new_balance' => $newBalance,
                    'reward_id' => $reward->id,
                    'reward' => [
                        'id' => $reward->id,
                        'title' => $reward->title,
                    ],
                    'card_id' => $card->id,
                    'member_id' => $member->id,
                ],
            ]);
        } catch (\Exception $e) {
            report($e);

            return $this->jsonError(
                code: 'REDEMPTION_FAILED',
                message: 'Unable to process this redemption. Please verify the input and try again.',
                status: 422,
                retryStrategy: 'fix_request',
            );
        }
    }
}
