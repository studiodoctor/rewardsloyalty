<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Agent API: Member read access for partners.
 *
 * READ-ONLY + BALANCE. No member CREATE/UPDATE/DELETE via agent API.
 * Members self-register or are auto-enrolled via transactions.
 * This matches MemberDataDefinition which only supports 'list' and 'export'.
 *
 * @see RewardLoyalty-100b-phase2-core-endpoints.md §2.5
 */

namespace App\Http\Controllers\Api\Agent\Partner;

use App\Http\Controllers\Api\Agent\BaseAgentController;
use App\Http\Controllers\Api\Agent\Concerns\EnforcesPartnerGates;
use App\Models\Card;
use App\Models\Member;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentMemberController extends BaseAgentController
{
    use EnforcesPartnerGates;

    /**
     * GET /api/agent/v1/partner/members
     * Scope: read
     *
     * Returns members that have interacted with the partner's cards.
     * Searchable by name, email, member_number.
     */
    public function index(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        $query = Member::whereHas('cards', function ($q) use ($partner) {
            $q->where('cards.created_by', $partner->id);
        });

        // Search
        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('member_number', 'like', "%{$search}%")
                    ->orWhere('unique_identifier', 'like', "%{$search}%");
            });
        }

        $members = $query->orderBy('created_at', 'desc')
            ->paginate($this->getPerPage());

        return $this->jsonSuccess([
            'data' => $members->getCollection()->map(
                fn (Member $member) => $this->serializePartnerMember($member)
            )->values(),
            'pagination' => $this->paginationMeta($members),
        ]);
    }

    /**
     * GET /api/agent/v1/partner/members/{id}
     * Scope: read
     *
     * Returns member details. Accepts UUID, email, member_number,
     * or unique_identifier (uses BaseAgentController::resolveMember).
     */
    public function show(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        $member = $this->resolveMember($id);

        if (! $member) {
            return $this->jsonNotFound('Member');
        }

        // Verify member has interacted with this partner's cards (tenant isolation)
        $hasInteraction = $member->cards()
            ->where('cards.created_by', $partner->id)
            ->exists();

        if (! $hasInteraction) {
            return $this->jsonNotFound('Member');
        }

        return $this->jsonSuccess([
            'data' => $this->serializePartnerMember($member),
        ]);
    }

    /**
     * GET /api/agent/v1/partner/members/{id}/balance/{cardId}
     * Scope: read
     *
     * Returns the member's point balance for a specific card.
     */
    public function balance(Request $request, string $id, string $cardId): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        $member = $this->resolveMember($id);
        if (! $member) {
            return $this->jsonNotFound('Member');
        }

        // Verify member has interacted with this partner's cards (tenant isolation)
        $hasInteraction = $member->cards()
            ->where('cards.created_by', $partner->id)
            ->exists();

        if (! $hasInteraction) {
            return $this->jsonNotFound('Member');
        }

        $card = Card::where('id', $cardId)
            ->where('created_by', $partner->id)
            ->first();

        if (! $card) {
            return $this->jsonNotFound('Card');
        }

        $balance = $card->getMemberBalance($member);

        return $this->jsonSuccess([
            'data' => [
                'member_id' => $member->id,
                'card_id' => $card->id,
                'balance' => $balance,
                'currency' => $card->currency,
            ],
        ]);
    }

    private function serializePartnerMember(Member $member): array
    {
        return [
            'id' => $member->id,
            'unique_identifier' => $member->unique_identifier,
            'name' => $member->name,
            'email' => $member->email,
            'locale' => $member->locale,
            'currency' => $member->currency,
            'time_zone' => $member->time_zone,
            'last_login_at' => $this->serializeDateTime($member->last_login_at),
            'created_at' => $this->serializeDateTime($member->created_at),
            'updated_at' => $this->serializeDateTime($member->updated_at),
            'avatar' => $member->avatar,
            'is_anonymous' => $member->email === null,
        ];
    }
}
