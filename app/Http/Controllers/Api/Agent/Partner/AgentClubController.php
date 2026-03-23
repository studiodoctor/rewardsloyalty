<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Agent API: Club management for partners.
 * Clubs are organizational units that group cards, staff, and tiers.
 *
 * Mirror source: PartnerClubController + ClubDataDefinition
 * Ownership filter: Club::where('created_by', $partner->id)
 *
 * @see RewardLoyalty-100b-phase2-core-endpoints.md §2.1
 */

namespace App\Http\Controllers\Api\Agent\Partner;

use App\Http\Controllers\Api\Agent\BaseAgentController;
use App\Http\Controllers\Api\Agent\Concerns\EnforcesPartnerGates;
use App\Models\Club;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AgentClubController extends BaseAgentController
{
    use EnforcesPartnerGates;

    /**
     * GET /api/agent/v1/partner/clubs
     * Scope: read
     */
    public function index(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read', 'write:clubs')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        $clubs = Club::where('created_by', $partner->id)
            ->orderBy('created_at', 'desc')
            ->paginate($this->getPerPage());

        return $this->jsonPaginated($clubs);
    }

    /**
     * GET /api/agent/v1/partner/clubs/{id}
     * Scope: read
     */
    public function show(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read', 'write:clubs')) {
            return $denied;
        }

        $partner = $this->getPartner($request);
        $club = Club::where('id', $id)
            ->where('created_by', $partner->id)
            ->first();

        if (! $club) {
            return $this->jsonNotFound('Club');
        }

        return $this->jsonResource($club);
    }

    /**
     * POST /api/agent/v1/partner/clubs
     * Scope: write:clubs
     */
    public function store(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:clubs')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray());
        }

        $club = Club::create([
            'name' => $request->input('name'),
            'is_active' => $request->input('is_active', true),
            'created_by' => $partner->id,
        ]);

        return $this->jsonResource($club, 201);
    }

    /**
     * PUT /api/agent/v1/partner/clubs/{id}
     * Scope: write:clubs
     */
    public function update(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:clubs')) {
            return $denied;
        }

        $partner = $this->getPartner($request);
        $club = Club::where('id', $id)
            ->where('created_by', $partner->id)
            ->first();

        if (! $club) {
            return $this->jsonNotFound('Club');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:120',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray());
        }

        $club->update(array_filter([
            'name' => $request->input('name'),
            'is_active' => $request->input('is_active'),
            'updated_by' => $partner->id,
        ], fn ($v) => $v !== null));

        return $this->jsonResource($club->fresh());
    }

    /**
     * DELETE /api/agent/v1/partner/clubs/{id}
     * Scope: write:clubs
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:clubs')) {
            return $denied;
        }

        $partner = $this->getPartner($request);
        $club = Club::where('id', $id)
            ->where('created_by', $partner->id)
            ->first();

        if (! $club) {
            return $this->jsonNotFound('Club');
        }

        // Prevent deleting undeletable clubs (e.g., the default club)
        if ($club->is_undeletable ?? false) {
            return $this->jsonError(
                code: 'RESOURCE_PROTECTED',
                message: 'This club cannot be deleted.',
                status: 422,
                retryStrategy: 'no_retry',
            );
        }

        $club->delete();

        return $this->jsonSuccess(['message' => 'Club deleted.']);
    }
}
