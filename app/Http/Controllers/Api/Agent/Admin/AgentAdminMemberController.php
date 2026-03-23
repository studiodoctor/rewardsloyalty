<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Agent API: Admin platform-wide member access (read-only).
 *
 * Provides admin-level member search and details across all partners.
 * This is strictly read-only — member lifecycle is managed through
 * partner transactions (auto-enrollment) or member self-service.
 *
 * Endpoints:
 * - GET /admin/members          → List/search all members
 * - GET /admin/members/{id}     → Show member details with card balances
 *
 * @see RewardLoyalty-100d-phase4-advanced.md §1.2
 */

namespace App\Http\Controllers\Api\Agent\Admin;

use App\Http\Controllers\Api\Agent\BaseAgentController;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentAdminMemberController extends BaseAgentController
{
    /**
     * GET /api/agent/v1/admin/members
     * Scope: read:members
     *
     * List all members platform-wide. Supports search by name/email.
     */
    public function index(Request $request): JsonResponse
    {
        if ($denied = $this->requireAdminScope($request, 'read:members')) {
            return $denied;
        }

        $query = Member::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('unique_identifier', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $members = $query->orderBy('created_at', 'desc')
            ->paginate($this->getPerPage());

        $data = $members->getCollection()->map(fn (Member $m) => $this->serializeMember($m));

        return $this->jsonSuccess([
            'data' => $data,
            'pagination' => [
                'current_page' => $members->currentPage(),
                'last_page' => $members->lastPage(),
                'per_page' => $members->perPage(),
                'total' => $members->total(),
            ],
        ]);
    }

    /**
     * GET /api/agent/v1/admin/members/{id}
     * Scope: read:members
     *
     * Show member details with card enrollment info.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireAdminScope($request, 'read:members')) {
            return $denied;
        }

        $member = Member::find($id);
        if (! $member) {
            return $this->jsonNotFound('Member');
        }

        $cardBalances = $member->cards()
            ->with('club:id,name')
            ->get()
            ->map(fn ($card) => [
                'card_id' => $card->id,
                'card_title' => $card->title,
                'club_name' => $card->club?->name,
                'balance' => $card->getMemberBalance($member),
                'currency' => $card->currency,
            ]);

        return $this->jsonSuccess([
            'data' => array_merge(
                $this->serializeMember($member),
                ['card_balances' => $cardBalances],
            ),
        ]);
    }

    // ═════════════════════════════════════════════════════════════════════════
    // HELPERS
    // ═════════════════════════════════════════════════════════════════════════

    private function requireAdminScope(Request $request, string $scope): ?JsonResponse
    {
        $agentKey = $request->attributes->get('agent_key');

        if (! $agentKey || ! $agentKey->hasAnyScope([$scope])) {
            return $this->jsonScopeError($scope);
        }

        return null;
    }

    private function serializeMember(Member $member): array
    {
        return [
            'id' => $member->id,
            'name' => $member->name,
            'email' => $member->email,
            'unique_identifier' => $member->unique_identifier,
            'locale' => $member->locale,
            'is_active' => (bool) $member->is_active,
            'is_anonymous' => $member->email === null,
            'created_at' => $member->created_at,
        ];
    }
}
