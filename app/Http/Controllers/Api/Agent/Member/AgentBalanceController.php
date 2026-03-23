<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Agent API: Member wallet — balance, cards, and transaction history.
 *
 * This is the most consumed member endpoint. The "wallet overview" widget
 * on webshops calls GET /balance to show what the member has across all
 * loyalty programs they participate in.
 *
 * Design:
 * - Members can only see cards they've transacted with (enrolled via card_member pivot)
 * - Card details stay member-safe and do not expose internal club structure
 * - Balance is calculated from active (non-expired) transactions
 * - Transaction history is paginated and filtered by card
 * - Transaction responses are filtered to member-relevant fields only
 *   (no partner emails, internal config values, or admin metadata)
 *
 * Scopes:
 *   read → All endpoints in this controller
 *
 * @see Card::getMemberBalance()
 * @see RewardLoyalty-100d-phase4-advanced.md §2.1
 */

namespace App\Http\Controllers\Api\Agent\Member;

use App\Http\Controllers\Api\Agent\BaseAgentController;
use App\Http\Controllers\Api\Agent\Concerns\EnforcesMemberGates;
use App\Models\Card;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentBalanceController extends BaseAgentController
{
    use EnforcesMemberGates;

    /**
     * GET /api/agent/v1/member/balance
     * Scope: read
     *
     * Returns all card balances for this member in one call.
     * Primary use case: webshop "wallet overview" widget.
     */
    public function balance(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read')) {
            return $denied;
        }

        $member = $this->getMember($request);

        $cards = $member->cards()
            ->get()
            ->map(fn (Card $card) => [
                'card_id' => $card->id,
                'card_title' => $card->title,
                'balance' => $card->getMemberBalance($member),
                'currency' => $card->currency ?? 'points',
            ]);

        return $this->jsonSuccess(['data' => $cards]);
    }

    /**
     * GET /api/agent/v1/member/cards
     * Scope: read
     *
     * List all cards the member has transacted with, paginated.
     * Returns member-relevant card details only.
     */
    public function cards(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read')) {
            return $denied;
        }

        $member = $this->getMember($request);

        $cards = $member->cards()->paginate($this->getPerPage());

        $items = $cards->getCollection()->map(function (Card $card) use ($member) {
            return $this->serializeMemberCard($card, $member);
        });

        return $this->jsonSuccess([
            'data' => $items,
            'pagination' => [
                'current_page' => $cards->currentPage(),
                'last_page' => $cards->lastPage(),
                'per_page' => $cards->perPage(),
                'total' => $cards->total(),
            ],
        ]);
    }

    /**
     * GET /api/agent/v1/member/cards/{id}
     * Scope: read
     *
     * Show a single card's details + member-specific balance.
     * Card must belong to the member's enrolled cards.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read')) {
            return $denied;
        }

        $member = $this->getMember($request);

        // Only show cards the member is enrolled in (via card_member pivot)
        $card = $member->cards()
            ->where('cards.id', $id)
            ->first();

        if (! $card) {
            return $this->jsonNotFound('Card');
        }

        return $this->jsonSuccess([
            'data' => $this->serializeMemberCard($card, $member),
        ]);
    }

    /**
     * GET /api/agent/v1/member/transactions
     * Scope: read
     *
     * Transaction history across all cards, paginated.
     * Most recent first. Returns only member-relevant fields.
     */
    public function transactions(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read')) {
            return $denied;
        }

        $member = $this->getMember($request);

        $transactions = Transaction::where('member_id', $member->id)
            ->with(['card:id,title'])
            ->orderBy('created_at', 'desc')
            ->paginate($this->getPerPage());

        $items = $transactions->getCollection()->map(
            fn (Transaction $tx) => $this->serializeMemberTransaction($tx)
        );

        return $this->jsonSuccess([
            'data' => $items,
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * GET /api/agent/v1/member/transactions/{cardId}
     * Scope: read
     *
     * Transaction history for a specific card, paginated.
     * Card must belong to the member's enrolled cards.
     */
    public function cardTransactions(Request $request, string $cardId): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read')) {
            return $denied;
        }

        $member = $this->getMember($request);

        // Verify the member is enrolled in this card
        $isEnrolled = $member->cards()->where('cards.id', $cardId)->exists();
        if (! $isEnrolled) {
            return $this->jsonNotFound('Card');
        }

        $transactions = Transaction::where('member_id', $member->id)
            ->where('card_id', $cardId)
            ->orderBy('created_at', 'desc')
            ->paginate($this->getPerPage());

        $items = $transactions->getCollection()->map(
            fn (Transaction $tx) => $this->serializeMemberTransaction($tx)
        );

        return $this->jsonSuccess([
            'data' => $items,
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    // ═════════════════════════════════════════════════════════════════════════
    // MEMBER-SAFE SERIALIZERS
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Serialize a transaction for member-facing API responses.
     *
     * Only includes fields that a member would see in the dashboard
     * history component. Excludes: partner emails, staff emails,
     * internal config values, admin metadata, created_by, etc.
     *
     * @see resources/views/components/member/history.blade.php
     */
    private function serializeMemberTransaction(Transaction $tx): array
    {
        return [
            'id' => $tx->id,
            'card_id' => $tx->card_id,
            'card_title' => $tx->card?->title,
            'event' => $tx->event,
            'points' => $tx->points,
            'points_used' => $tx->points_used,
            'purchase_amount' => $tx->purchase_amount,
            'currency' => $tx->currency,
            'reward_title' => $tx->reward_title,
            'reward_points' => $tx->reward_points,
            'note' => $tx->note,
            'expires_at' => $tx->expires_at?->toIso8601String(),
            'created_at' => $tx->created_at?->toIso8601String(),
        ];
    }

    /**
     * Serialize a card for member-facing API responses.
     *
     * Includes the member's balance and card display info.
     * Excludes internal admin fields and club ownership metadata.
     */
    private function serializeMemberCard(Card $card, $member): array
    {
        return [
            'id' => $card->id,
            'name' => $card->name,
            'title' => $card->title,
            'description' => $card->description ?? null,
            'currency' => $card->currency ?? 'points',
            'balance' => $card->getMemberBalance($member),
            'bg_color' => $card->bg_color,
            'text_color' => $card->text_color,
            'is_active' => (bool) $card->is_active,
        ];
    }
}
