<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Agent API: Loyalty Card management for partners.
 *
 * Cards define the points economy: currency, earning rate, limits, expiry.
 * This controller handles CRUD — transaction operations live in
 * AgentTransactionController which uses TransactionService.
 *
 * Mirror source: PartnerCardController + CardDataDefinition
 * Ownership filter: Card::where('created_by', $partner->id)
 *
 * @see RewardLoyalty-100b-phase2-core-endpoints.md §2.2
 */

namespace App\Http\Controllers\Api\Agent\Partner;

use App\Http\Controllers\Api\Agent\BaseAgentController;
use App\Http\Controllers\Api\Agent\Concerns\EnforcesPartnerGates;
use App\Models\Card;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AgentCardController extends BaseAgentController
{
    use EnforcesPartnerGates;

    /**
     * Validation rules derived from CardDataDefinition fields.
     * Translatable fields accept both JSON objects (multi-locale) and strings.
     */
    private function storeRules(): array
    {
        return [
            'club_id' => 'required|uuid|exists:clubs,id',
            'name' => 'required|string|max:250',
            'head' => 'nullable',
            'title' => 'nullable',
            'description' => 'nullable',
            'currency' => 'required|string|size:3',
            'points_per_currency' => 'required|numeric|min:0|max:100000',
            'currency_unit_amount' => 'required|numeric|min:1|max:1000000',
            'min_points_per_purchase' => 'required|numeric|min:0|max:10000000',
            'max_points_per_purchase' => 'required|numeric|min:0|max:10000000',
            'initial_bonus_points' => 'nullable|numeric|min:0|max:10000000',
            'points_expiration_months' => 'required|numeric|min:1|max:1200',
            'issue_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
            'is_visible_by_default' => 'nullable|boolean',
            'bg_color' => 'nullable|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'bg_color_opacity' => 'nullable|numeric|min:0|max:100',
            'text_color' => 'nullable|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
        ];
    }

    /**
     * GET /api/agent/v1/partner/cards
     * Scope: read
     */
    public function index(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read', 'write:cards')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        if ($error = $this->checkPermission($partner, 'loyalty_cards_permission')) {
            return $error;
        }

        $cards = Card::where('created_by', $partner->id)
            ->orderBy('created_at', 'desc')
            ->paginate($this->getPerPage());

        return $this->jsonPaginated($cards);
    }

    /**
     * GET /api/agent/v1/partner/cards/{id}
     * Scope: read
     */
    public function show(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read', 'write:cards')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        if ($error = $this->checkPermission($partner, 'loyalty_cards_permission')) {
            return $error;
        }

        $card = Card::where('id', $id)
            ->where('created_by', $partner->id)
            ->first();

        if (! $card) {
            return $this->jsonNotFound('Card');
        }

        return $this->jsonResource($card);
    }

    /**
     * POST /api/agent/v1/partner/cards
     * Scope: write:cards
     */
    public function store(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:cards')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        if ($error = $this->checkPermission($partner, 'loyalty_cards_permission')) {
            return $error;
        }
        if ($error = $this->checkLimit($partner, 'loyalty_cards_limit', Card::class, 'Loyalty cards')) {
            return $error;
        }

        $validator = Validator::make($request->all(), $this->storeRules());

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray());
        }

        // Verify club ownership
        $club = $this->resolveClub($partner, $request->input('club_id'));
        if ($club instanceof JsonResponse) {
            return $club;
        }

        $card = Card::create(array_merge(
            $validator->validated(),
            ['created_by' => $partner->id],
        ));

        return $this->jsonResource($card, 201);
    }

    /**
     * PUT /api/agent/v1/partner/cards/{id}
     * Scope: write:cards
     */
    public function update(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:cards')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        if ($error = $this->checkPermission($partner, 'loyalty_cards_permission')) {
            return $error;
        }

        $card = Card::where('id', $id)
            ->where('created_by', $partner->id)
            ->first();

        if (! $card) {
            return $this->jsonNotFound('Card');
        }

        // All fields optional on update
        $rules = array_map(fn ($rule) => str_replace('required|', 'nullable|', $rule), $this->storeRules());
        $rules['club_id'] = 'nullable|uuid|exists:clubs,id';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray());
        }

        // If club_id is being changed, verify ownership
        if ($request->has('club_id')) {
            $club = $this->resolveClub($partner, $request->input('club_id'));
            if ($club instanceof JsonResponse) {
                return $club;
            }
        }

        $card->update(array_merge(
            array_filter($validator->validated(), fn ($v) => $v !== null),
            ['updated_by' => $partner->id],
        ));

        return $this->jsonResource($card->fresh());
    }

    /**
     * DELETE /api/agent/v1/partner/cards/{id}
     * Scope: write:cards
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:cards')) {
            return $denied;
        }

        $partner = $this->getPartner($request);
        $card = Card::where('id', $id)
            ->where('created_by', $partner->id)
            ->first();

        if (! $card) {
            return $this->jsonNotFound('Card');
        }

        $card->delete();

        return $this->jsonSuccess(['message' => 'Card deleted.']);
    }
}
