<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Agent API: Tier CRUD for partners.
 * Tiers are loyalty levels within a club (Bronze, Silver, Gold).
 *
 * @see RewardLoyalty-100b-phase2-core-endpoints.md §2.8
 */

namespace App\Http\Controllers\Api\Agent\Partner;

use App\Http\Controllers\Api\Agent\BaseAgentController;
use App\Http\Controllers\Api\Agent\Concerns\EnforcesPartnerGates;
use App\Models\Tier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AgentTierController extends BaseAgentController
{
    use EnforcesPartnerGates;

    private function storeRules(): array
    {
        return [
            'club_id' => 'required|uuid|exists:clubs,id',
            'name' => 'required',
            'description' => 'nullable',
            'level' => 'required|integer|min:0',
            'points_threshold' => 'nullable|integer|min:0',
            'points_multiplier' => 'nullable|numeric|min:1|max:100',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function index(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read', 'write:tiers')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        $tiers = Tier::where('created_by', $partner->id)
            ->orderBy('level')
            ->paginate($this->getPerPage());

        return $this->jsonPaginated($tiers);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read', 'write:tiers')) {
            return $denied;
        }

        $partner = $this->getPartner($request);
        $tier = Tier::where('id', $id)
            ->where('created_by', $partner->id)
            ->first();

        if (! $tier) {
            return $this->jsonNotFound('Tier');
        }

        return $this->jsonResource($tier);
    }

    public function store(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:tiers')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        $payload = $request->all();

        if ($error = $this->rejectDeprecatedFields($payload, [
            'points_required' => 'points_threshold',
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

        $tier = Tier::create(array_merge(
            $validated,
            ['created_by' => $partner->id],
        ));

        return $this->jsonResource($tier, 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:tiers')) {
            return $denied;
        }

        $partner = $this->getPartner($request);
        $tier = Tier::where('id', $id)
            ->where('created_by', $partner->id)
            ->first();

        if (! $tier) {
            return $this->jsonNotFound('Tier');
        }

        $rules = array_map(fn ($rule) => str_replace('required|', 'nullable|', $rule), $this->storeRules());
        $rules['club_id'] = 'nullable|uuid|exists:clubs,id';
        $payload = $request->all();

        if ($error = $this->rejectDeprecatedFields($payload, [
            'points_required' => 'points_threshold',
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

        $tier->update(array_merge(
            array_filter($validated, fn ($v) => $v !== null),
            ['updated_by' => $partner->id],
        ));

        return $this->jsonResource($tier->fresh());
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:tiers')) {
            return $denied;
        }

        $partner = $this->getPartner($request);
        $tier = Tier::where('id', $id)
            ->where('created_by', $partner->id)
            ->first();

        if (! $tier) {
            return $this->jsonNotFound('Tier');
        }

        $tier->delete();

        return $this->jsonSuccess(['message' => 'Tier deleted.']);
    }
}
