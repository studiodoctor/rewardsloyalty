<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Shared partner permission + limit enforcement for agent controllers.
 *
 * This trait extracts the common patterns that every partner agent controller
 * needs: getting the partner from the agent key, checking feature permissions,
 * and enforcing resource limits. It's lean and focused — no business logic,
 * just gate checks that return null (pass) or JsonResponse (fail).
 *
 * Covers permission gates:
 * - loyalty_cards_permission, stamp_cards_permission, vouchers_permission
 * - voucher_batches_permission, email_campaigns_permission, activity_permission
 *
 * Covers limit gates:
 * - loyalty_cards_limit, stamp_cards_limit, vouchers_limit
 * - staff_members_limit, rewards_limit, agent_keys_limit
 *
 * @see App\Models\Partner (permission/limit attributes via meta JSON)
 * @see RewardLoyalty-100-agent.md §A5 and §A11
 */

namespace App\Http\Controllers\Api\Agent\Concerns;

use App\Models\AgentKey;
use App\Models\Club;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait EnforcesPartnerGates
{
    /**
     * Get the authenticated partner from the agent key.
     *
     */
    protected function getPartner(Request $request): Partner
    {
        return $request->attributes->get('agent_key')->getPartner();
    }

    /**
     * Get the AgentKey model instance from the request.
     */
    protected function getAgentKey(Request $request): AgentKey
    {
        return $request->attributes->get('agent_key');
    }

    /**
     * Check a partner feature permission.
     *
     * Returns null on success, JsonResponse on failure.
     * This pattern allows callers to do:
     *   if ($error = $this->checkPermission($partner, 'loyalty_cards_permission')) return $error;
     */
    protected function checkPermission(Partner $partner, string $permission): ?JsonResponse
    {
        if (! $partner->$permission) {
            return $this->jsonError(
                code: 'FEATURE_DISABLED',
                message: "The '{$permission}' feature is not enabled for this partner.",
                status: 403,
                retryStrategy: 'contact_support',
                details: ['permission' => $permission],
            );
        }

        return null;
    }

    /**
     * Check a partner resource limit.
     *
     * Returns null on success, JsonResponse on failure.
     *
     * @param  Partner  $partner  The partner to check
     * @param  string  $limitAttribute  Partner attribute name (e.g., 'loyalty_cards_limit')
     * @param  string  $modelClass  Model class to count (e.g., Card::class)
     * @param  string  $resourceName  Human-readable name for error message
     */
    protected function checkLimit(
        Partner $partner,
        string $limitAttribute,
        string $modelClass,
        string $resourceName,
    ): ?JsonResponse {
        $limit = (int) $partner->$limitAttribute;

        // -1 = unlimited
        if ($limit === -1) {
            return null;
        }

        $currentCount = $modelClass::where('created_by', $partner->id)->count();

        if ($currentCount >= $limit) {
            return $this->jsonError(
                code: 'LIMIT_REACHED',
                message: "{$resourceName} limit reached ({$currentCount}/{$limit}).",
                status: 422,
                retryStrategy: 'contact_support',
                details: [
                    'resource' => $resourceName,
                    'current' => $currentCount,
                    'limit' => $limit,
                ],
            );
        }

        return null;
    }

    /**
     * Resolve and validate club ownership.
     *
     * Returns the Club on success, or a JsonResponse error on failure.
     * Use like:
     *   $club = $this->resolveClub($partner, $request->input('club_id'));
     *   if ($club instanceof JsonResponse) return $club;
     */
    protected function resolveClub(Partner $partner, ?string $clubId): Club|JsonResponse
    {
        if (! $clubId) {
            return $this->jsonError(
                code: 'VALIDATION_FAILED',
                message: 'club_id is required.',
                status: 422,
                retryStrategy: 'fix_request',
            );
        }

        $club = Club::where('id', $clubId)
            ->where('created_by', $partner->id)
            ->first();

        if (! $club) {
            return $this->jsonNotFound('Club');
        }

        return $club;
    }

    /**
     * Require one of the specified scopes on the agent key.
     *
     * Returns null on success, JsonResponse on failure.
     * Pass multiple scopes for OR-logic: any one grants access.
     *
     * Usage:
     *   if ($denied = $this->requireScope($request, 'write:cards')) return $denied;
     *   if ($denied = $this->requireScope($request, 'read', 'write:cards')) return $denied;
     */
    protected function requireScope(Request $request, string ...$scopes): ?JsonResponse
    {
        $agentKey = $this->getAgentKey($request);

        if (! $agentKey->hasAnyScope($scopes)) {
            return $this->jsonScopeError($scopes[0]);
        }

        return null;
    }
}
