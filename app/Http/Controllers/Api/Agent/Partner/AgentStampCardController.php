<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Agent API: Stamp Card CRUD + stamp/redeem operations for partners.
 *
 * Uses StampService for all business logic (eligibility, enrollment,
 * completion detection, event dispatching).
 *
 * @see RewardLoyalty-100b-phase2-core-endpoints.md §2.6
 */

namespace App\Http\Controllers\Api\Agent\Partner;

use App\Http\Controllers\Api\Agent\BaseAgentController;
use App\Http\Controllers\Api\Agent\Concerns\EnforcesPartnerGates;
use App\Models\StampCard;
use App\Services\StampService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AgentStampCardController extends BaseAgentController
{
    use EnforcesPartnerGates;

    public function __construct(
        private StampService $stampService,
    ) {}

    private function storeRules(): array
    {
        return [
            'club_id' => 'required|uuid|exists:clubs,id',
            'name' => 'required|string|max:250',
            'head' => 'nullable',
            'title' => 'nullable',
            'description' => 'nullable',
            'reward_title' => 'nullable',
            'stamps_required' => 'required|integer|min:1|max:100',
            'stamps_per_purchase' => 'nullable|integer|min:1|max:100',
            'max_stamps_per_transaction' => 'nullable|integer|min:1',
            'max_stamps_per_day' => 'nullable|integer|min:1',
            'stamps_expire_days' => 'nullable|integer|min:1|max:365',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'requires_physical_claim' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function index(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read', 'write:stamps')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        if ($error = $this->checkPermission($partner, 'stamp_cards_permission')) {
            return $error;
        }

        $stampCards = StampCard::where('created_by', $partner->id)
            ->orderBy('created_at', 'desc')
            ->paginate($this->getPerPage());

        return $this->jsonPaginated($stampCards);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read', 'write:stamps')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        if ($error = $this->checkPermission($partner, 'stamp_cards_permission')) {
            return $error;
        }

        $stampCard = StampCard::where('id', $id)
            ->where('created_by', $partner->id)
            ->first();

        if (! $stampCard) {
            return $this->jsonNotFound('Stamp card');
        }

        return $this->jsonResource($stampCard);
    }

    public function store(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:stamps')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        if ($error = $this->checkPermission($partner, 'stamp_cards_permission')) {
            return $error;
        }
        if ($error = $this->checkLimit($partner, 'stamp_cards_limit', StampCard::class, 'Stamp cards')) {
            return $error;
        }

        $payload = $request->all();

        if ($error = $this->rejectDeprecatedFields($payload, [
            'require_staff_for_redemption' => 'requires_physical_claim',
            'stamp_expiry_days' => 'stamps_expire_days',
        ])) {
            return $error;
        }

        $validator = Validator::make($payload, $this->storeRules());

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray());
        }

        $validated = $validator->validated();

        $club = $this->resolveClub($partner, $validated['club_id']);
        if ($club instanceof JsonResponse) {
            return $club;
        }

        $stampCard = StampCard::create(array_merge(
            $validated,
            ['created_by' => $partner->id],
        ));

        return $this->jsonResource($stampCard, 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:stamps')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        if ($error = $this->checkPermission($partner, 'stamp_cards_permission')) {
            return $error;
        }

        $stampCard = StampCard::where('id', $id)
            ->where('created_by', $partner->id)
            ->first();

        if (! $stampCard) {
            return $this->jsonNotFound('Stamp card');
        }

        $rules = array_map(fn ($rule) => str_replace('required|', 'nullable|', $rule), $this->storeRules());
        $rules['club_id'] = 'nullable|uuid|exists:clubs,id';
        $payload = $request->all();

        if ($error = $this->rejectDeprecatedFields($payload, [
            'require_staff_for_redemption' => 'requires_physical_claim',
            'stamp_expiry_days' => 'stamps_expire_days',
        ])) {
            return $error;
        }

        $validator = Validator::make($payload, $rules);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray());
        }

        $validated = $validator->validated();

        if (array_key_exists('club_id', $validated)) {
            $club = $this->resolveClub($partner, $validated['club_id']);
            if ($club instanceof JsonResponse) {
                return $club;
            }
        }

        $stampCard->update(array_merge(
            array_filter($validated, fn ($v) => $v !== null),
            ['updated_by' => $partner->id],
        ));

        return $this->jsonResource($stampCard->fresh());
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:stamps')) {
            return $denied;
        }

        $partner = $this->getPartner($request);
        $stampCard = StampCard::where('id', $id)
            ->where('created_by', $partner->id)
            ->first();

        if (! $stampCard) {
            return $this->jsonNotFound('Stamp card');
        }

        $stampCard->delete();

        return $this->jsonSuccess(['message' => 'Stamp card deleted.']);
    }

    // ═══════════════════════════════════════════════════════════════════
    // STAMP OPERATIONS (via StampService)
    // ═══════════════════════════════════════════════════════════════════

    /**
     * POST /api/agent/v1/partner/stamp-cards/{id}/stamps
     * Scope: write:stamps
     *
     * Add stamps to a member's stamp card.
     */
    public function addStamps(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:stamps')) {
            return $denied;
        }

        $partner = $this->getPartner($request);
        $stampCard = StampCard::where('id', $id)
            ->where('created_by', $partner->id)
            ->first();

        if (! $stampCard) {
            return $this->jsonNotFound('Stamp card');
        }

        $validator = Validator::make($request->all(), [
            'member_identifier' => 'required|string',
            'stamps' => 'nullable|integer|min:1|max:100',
            'purchase_amount' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray());
        }

        $member = $this->resolveMember($request->input('member_identifier'));
        if (! $member) {
            return $this->jsonNotFound('Member');
        }

        $result = $this->stampService->addStamp(
            card: $stampCard,
            member: $member,
            staff: null,
            stamps: $request->input('stamps', 1),
            purchaseAmount: $request->input('purchase_amount') ? (float) $request->input('purchase_amount') : null,
            note: $request->input('note'),
        );

        if (! $result['success']) {
            return $this->jsonError(
                code: 'STAMP_FAILED',
                message: $result['error'] ?? 'Unable to add stamps.',
                status: 422,
            );
        }

        return $this->jsonSuccess([
            'data' => [
                'stamps_added' => $result['stamps_added'],
                'current_stamps' => $result['current_stamps'],
                'stamps_required' => $result['stamps_required'],
                'completed' => $result['completed'],
                'pending_rewards' => $result['pending_rewards'],
            ],
        ], 201);
    }

    /**
     * POST /api/agent/v1/partner/stamp-cards/{id}/redeem
     * Scope: write:stamps
     *
     * Redeem a pending stamp reward.
     */
    public function redeemStampReward(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:stamps')) {
            return $denied;
        }

        $partner = $this->getPartner($request);
        $stampCard = StampCard::where('id', $id)
            ->where('created_by', $partner->id)
            ->first();

        if (! $stampCard) {
            return $this->jsonNotFound('Stamp card');
        }

        $validator = Validator::make($request->all(), [
            'member_identifier' => 'required|string',
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray());
        }

        $member = $this->resolveMember($request->input('member_identifier'));
        if (! $member) {
            return $this->jsonNotFound('Member');
        }

        $result = $this->stampService->redeemReward(
            card: $stampCard,
            member: $member,
            staff: null,
            note: $request->input('note'),
        );

        if (! $result['success']) {
            return $this->jsonError(
                code: 'REDEEM_FAILED',
                message: $result['error'] ?? 'Unable to redeem reward.',
                status: 422,
            );
        }

        return $this->jsonSuccess([
            'data' => [
                'reward_title' => $result['reward_title'],
                'reward_value' => $result['reward_value'],
                'remaining_rewards' => $result['remaining_rewards'],
            ],
        ]);
    }
}
