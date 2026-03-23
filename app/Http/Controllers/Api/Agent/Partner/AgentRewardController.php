<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Agent API: Reward management for partners.
 *
 * Rewards are redeemable items that members exchange points for.
 * Permission gate: loyalty_cards_permission (rewards require cards).
 *
 * Mirror source: RewardDataDefinition
 * Ownership filter: Reward::where('created_by', $partner->id)
 *
 * @see RewardLoyalty-100b-phase2-core-endpoints.md §2.3
 */

namespace App\Http\Controllers\Api\Agent\Partner;

use App\Http\Controllers\Api\Agent\BaseAgentController;
use App\Http\Controllers\Api\Agent\Concerns\EnforcesPartnerGates;
use App\Models\Reward;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AgentRewardController extends BaseAgentController
{
    use EnforcesPartnerGates;

    private function storeRules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'title' => 'nullable',
            'description' => 'nullable',
            'points' => 'required|numeric|min:0|max:10000000',
            'active_from' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function index(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read', 'write:rewards')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        if ($error = $this->checkPermission($partner, 'loyalty_cards_permission')) {
            return $error;
        }

        $rewards = Reward::where('created_by', $partner->id)
            ->orderBy('created_at', 'desc')
            ->paginate($this->getPerPage());

        return $this->jsonPaginated($rewards);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read', 'write:rewards')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        if ($error = $this->checkPermission($partner, 'loyalty_cards_permission')) {
            return $error;
        }

        $reward = Reward::where('id', $id)
            ->where('created_by', $partner->id)
            ->first();

        if (! $reward) {
            return $this->jsonNotFound('Reward');
        }

        return $this->jsonResource($reward);
    }

    public function store(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:rewards')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        if ($error = $this->checkPermission($partner, 'loyalty_cards_permission')) {
            return $error;
        }
        if ($error = $this->checkLimit($partner, 'rewards_limit', Reward::class, 'Rewards')) {
            return $error;
        }

        $validator = Validator::make($request->all(), $this->storeRules());

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray());
        }

        $reward = Reward::create(array_merge(
            $validator->validated(),
            ['created_by' => $partner->id],
        ));

        return $this->jsonResource($reward, 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:rewards')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        if ($error = $this->checkPermission($partner, 'loyalty_cards_permission')) {
            return $error;
        }

        $reward = Reward::where('id', $id)
            ->where('created_by', $partner->id)
            ->first();

        if (! $reward) {
            return $this->jsonNotFound('Reward');
        }

        $rules = array_map(fn ($rule) => str_replace('required|', 'nullable|', $rule), $this->storeRules());
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray());
        }

        $reward->update(array_merge(
            array_filter($validator->validated(), fn ($v) => $v !== null),
            ['updated_by' => $partner->id],
        ));

        return $this->jsonResource($reward->fresh());
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:rewards')) {
            return $denied;
        }

        $partner = $this->getPartner($request);
        $reward = Reward::where('id', $id)
            ->where('created_by', $partner->id)
            ->first();

        if (! $reward) {
            return $this->jsonNotFound('Reward');
        }

        $reward->delete();

        return $this->jsonSuccess(['message' => 'Reward deleted.']);
    }
}
