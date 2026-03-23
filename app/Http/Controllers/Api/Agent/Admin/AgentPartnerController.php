<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Agent API: Admin partner management.
 *
 * Manages partners (business owners) at the platform level.
 * Mirrors the AdminPartnerController but uses the agent auth pipeline.
 *
 * Endpoints:
 * - GET    /admin/partners              → List all partners (paginated, filterable)
 * - GET    /admin/partners/{id}         → Show partner details + permissions
 * - PATCH  /admin/partners/{id}/permissions → Update partner permissions & limits
 * - POST   /admin/partners/{id}/activate   → Reactivate a partner
 * - POST   /admin/partners/{id}/deactivate → Deactivate a partner
 *
 * Intentionally omitting create/update/delete — partners self-register
 * or are created via the admin dashboard. Bulk destructive operations
 * should not be exposed via agent keys.
 *
 * @see App\Http\Controllers\Api\AdminPartnerController (mirror source)
 * @see RewardLoyalty-100d-phase4-advanced.md §1
 */

namespace App\Http\Controllers\Api\Agent\Admin;

use App\Http\Controllers\Api\Agent\BaseAgentController;
use App\Models\AgentKey;
use App\Models\Card;
use App\Models\Member;
use App\Models\Partner;
use App\Models\StampCard;
use App\Models\Transaction;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentPartnerController extends BaseAgentController
{
    /**
     * GET /api/agent/v1/admin/partners
     * Scope: read:partners
     *
     * List all partners. Supports filtering by is_active and text search.
     */
    public function index(Request $request): JsonResponse
    {
        if ($denied = $this->requireAdminScope($request, 'read:partners')) {
            return $denied;
        }

        $query = Partner::query();

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $partners = $query->orderBy('created_at', 'desc')
            ->paginate($this->getPerPage());

        $data = $partners->getCollection()->map(fn (Partner $p) => $this->serializePartner($p));

        return $this->jsonSuccess([
            'data' => $data,
            'pagination' => [
                'current_page' => $partners->currentPage(),
                'last_page' => $partners->lastPage(),
                'per_page' => $partners->perPage(),
                'total' => $partners->total(),
            ],
        ]);
    }

    /**
     * GET /api/agent/v1/admin/partners/{id}
     * Scope: read:partners
     *
     * Show a single partner with their permissions and usage stats.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireAdminScope($request, 'read:partners')) {
            return $denied;
        }

        $partner = Partner::find($id);
        if (! $partner) {
            return $this->jsonNotFound('Partner');
        }

        return $this->jsonSuccess([
            'data' => array_merge(
                $this->serializePartner($partner),
                ['permissions' => $this->extractPermissions($partner)],
                ['usage' => $this->getUsageCounts($partner)],
            ),
        ]);
    }

    /**
     * PATCH /api/agent/v1/admin/partners/{id}/permissions
     * Scope: write:partners
     *
     * Update partner permissions and limits.
     */
    public function updatePermissions(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireAdminScope($request, 'write:partners')) {
            return $denied;
        }

        $partner = Partner::find($id);
        if (! $partner) {
            return $this->jsonNotFound('Partner');
        }

        $rules = [
            'loyalty_cards_permission' => 'nullable|boolean',
            'loyalty_cards_limit' => 'nullable|integer|min:-1',
            'stamp_cards_permission' => 'nullable|boolean',
            'stamp_cards_limit' => 'nullable|integer|min:-1',
            'vouchers_permission' => 'nullable|boolean',
            'voucher_batches_permission' => 'nullable|boolean',
            'vouchers_limit' => 'nullable|integer|min:-1',
            'rewards_limit' => 'nullable|integer|min:-1',
            'staff_members_limit' => 'nullable|integer|min:-1',
            'email_campaigns_permission' => 'nullable|boolean',
            'activity_permission' => 'nullable|boolean',
            'agent_api_permission' => 'nullable|boolean',
            'agent_keys_limit' => 'nullable|integer|min:-1',
            'cards_on_homepage' => 'nullable|boolean',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray());
        }

        $validated = $validator->validated();
        $meta = $partner->meta ?? [];

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                $meta[$key] = $value;
            }
        }

        $partner->meta = $meta;
        $partner->save();

        return $this->jsonSuccess([
            'data' => $this->extractPermissions($partner),
        ]);
    }

    /**
     * POST /api/agent/v1/admin/partners/{id}/activate
     * Scope: write:partners
     */
    public function activate(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireAdminScope($request, 'write:partners')) {
            return $denied;
        }

        $partner = Partner::find($id);
        if (! $partner) {
            return $this->jsonNotFound('Partner');
        }

        $partner->is_active = true;
        $partner->save();

        return $this->jsonSuccess([
            'data' => [
                'id' => $partner->id,
                'name' => $partner->name,
                'is_active' => true,
                'message' => 'Partner activated.',
            ],
        ]);
    }

    /**
     * POST /api/agent/v1/admin/partners/{id}/deactivate
     * Scope: write:partners
     */
    public function deactivate(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireAdminScope($request, 'write:partners')) {
            return $denied;
        }

        $partner = Partner::find($id);
        if (! $partner) {
            return $this->jsonNotFound('Partner');
        }

        $partner->is_active = false;
        $partner->save();

        return $this->jsonSuccess([
            'data' => [
                'id' => $partner->id,
                'name' => $partner->name,
                'is_active' => false,
                'message' => 'Partner deactivated. All associated agent keys are now invalid.',
            ],
        ]);
    }

    // ═════════════════════════════════════════════════════════════════════════
    // SCOPE ENFORCEMENT
    // ═════════════════════════════════════════════════════════════════════════

    private function requireAdminScope(Request $request, string $scope): ?JsonResponse
    {
        $agentKey = $request->attributes->get('agent_key');

        if (! $agentKey || ! $agentKey->hasAnyScope([$scope])) {
            return $this->jsonScopeError($scope);
        }

        return null;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // SERIALIZERS
    // ═════════════════════════════════════════════════════════════════════════

    private function serializePartner(Partner $partner): array
    {
        return [
            'id' => $partner->id,
            'name' => $partner->name,
            'email' => $partner->email,
            'locale' => $partner->locale,
            'currency' => $partner->currency,
            'time_zone' => $partner->time_zone,
            'is_active' => (bool) $partner->is_active,
            'avatar' => $partner->avatar,
            'created_at' => $partner->created_at,
        ];
    }

    private function extractPermissions(Partner $partner): array
    {
        $meta = $partner->meta ?? [];

        return [
            'loyalty_cards_permission' => $meta['loyalty_cards_permission'] ?? true,
            'loyalty_cards_limit' => $meta['loyalty_cards_limit'] ?? -1,
            'stamp_cards_permission' => $meta['stamp_cards_permission'] ?? true,
            'stamp_cards_limit' => $meta['stamp_cards_limit'] ?? -1,
            'vouchers_permission' => $meta['vouchers_permission'] ?? true,
            'voucher_batches_permission' => $meta['voucher_batches_permission'] ?? true,
            'vouchers_limit' => $meta['vouchers_limit'] ?? -1,
            'rewards_limit' => $meta['rewards_limit'] ?? -1,
            'staff_members_limit' => $meta['staff_members_limit'] ?? -1,
            'email_campaigns_permission' => $meta['email_campaigns_permission'] ?? true,
            'activity_permission' => $meta['activity_permission'] ?? true,
            'agent_api_permission' => $meta['agent_api_permission'] ?? false,
            'agent_keys_limit' => $meta['agent_keys_limit'] ?? 5,
            'cards_on_homepage' => $meta['cards_on_homepage'] ?? true,
        ];
    }

    private function getUsageCounts(Partner $partner): array
    {
        return [
            'loyalty_cards' => $partner->cards()->count(),
            'stamp_cards' => StampCard::where('created_by', $partner->id)->count(),
            'vouchers' => Voucher::where('created_by', $partner->id)->count(),
            'rewards' => $partner->rewards()->count(),
            'staff_members' => $partner->staff()->count(),
        ];
    }
}
