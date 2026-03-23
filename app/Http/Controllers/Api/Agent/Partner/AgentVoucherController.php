<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Agent API: Voucher CRUD + validate/redeem for partners.
 *
 * Uses VoucherService for validation and redemption business logic.
 * CRUD uses the canonical voucher schema (type, value, valid_from, valid_until,
 * max_uses_total, max_uses_per_member, code).
 *
 * @see VoucherService::validate()
 * @see VoucherService::redeem()
 */

namespace App\Http\Controllers\Api\Agent\Partner;

use App\Http\Controllers\Api\Agent\BaseAgentController;
use App\Http\Controllers\Api\Agent\Concerns\EnforcesPartnerGates;
use App\Models\Voucher;
use App\Services\VoucherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AgentVoucherController extends BaseAgentController
{
    use EnforcesPartnerGates;

    public function __construct(
        private VoucherService $voucherService,
    ) {}

    /**
     * Validation rules for creating a voucher using canonical field names.
     */
    private function storeRules(): array
    {
        return [
            'club_id' => 'required|uuid|exists:clubs,id',
            'code' => 'nullable|string|max:32',
            'name' => 'required|string|max:128',
            'title' => 'nullable',
            'description' => 'nullable',
            'type' => 'required|string|in:percentage,fixed_amount,free_product,bonus_points',
            'value' => 'required_unless:type,free_product,bonus_points|integer|min:0',
            'currency' => 'nullable|string|size:3',
            'points_value' => 'nullable|required_if:type,bonus_points|integer|min:1',
            'min_purchase_amount' => 'nullable|integer|min:0',
            'max_discount_amount' => 'nullable|integer|min:0',
            'max_uses_total' => 'nullable|integer|min:1',
            'max_uses_per_member' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date',
            'is_active' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
            'is_single_use' => 'nullable|boolean',
        ];
    }

    public function index(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read', 'write:vouchers')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        if ($error = $this->checkPermission($partner, 'vouchers_permission')) {
            return $error;
        }

        $vouchers = Voucher::where('created_by', $partner->id)
            ->orderBy('created_at', 'desc')
            ->paginate($this->getPerPage());

        return $this->jsonPaginated($vouchers);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read', 'write:vouchers')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        if ($error = $this->checkPermission($partner, 'vouchers_permission')) {
            return $error;
        }

        $voucher = Voucher::where('id', $id)
            ->where('created_by', $partner->id)
            ->first();

        if (! $voucher) {
            return $this->jsonNotFound('Voucher');
        }

        return $this->jsonResource($voucher);
    }

    public function store(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:vouchers')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        if ($error = $this->checkPermission($partner, 'vouchers_permission')) {
            return $error;
        }
        if ($error = $this->checkLimit($partner, 'vouchers_limit', Voucher::class, 'Vouchers')) {
            return $error;
        }

        $payload = $request->all();

        if ($error = $this->rejectDeprecatedFields($payload, [
            'discount_type' => 'type',
            'discount_value' => 'value',
            'max_uses' => 'max_uses_total',
            'issue_date' => 'valid_from',
            'expiration_date' => 'valid_until',
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

        // Generate unique code if not provided
        $code = $validated['code'] ?? null;
        if (! $code) {
            $code = $this->voucherService->generateUniqueCode($club->id);
        }

        $validated['code'] = $code;
        $validated['created_by'] = $partner->id;
        $validated['source'] = 'api';

        $voucher = Voucher::create($validated);

        return $this->jsonResource($voucher, 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:vouchers')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        if ($error = $this->checkPermission($partner, 'vouchers_permission')) {
            return $error;
        }

        $voucher = Voucher::where('id', $id)
            ->where('created_by', $partner->id)
            ->first();

        if (! $voucher) {
            return $this->jsonNotFound('Voucher');
        }

        // Update rules: everything optional except immutable fields
        $rules = [
            'name' => 'nullable|string|max:128',
            'title' => 'nullable',
            'description' => 'nullable',
            'type' => 'nullable|string|in:percentage,fixed_amount,free_product,bonus_points',
            'value' => 'nullable|integer|min:0',
            'currency' => 'nullable|string|size:3',
            'points_value' => 'nullable|integer|min:1',
            'min_purchase_amount' => 'nullable|integer|min:0',
            'max_discount_amount' => 'nullable|integer|min:0',
            'max_uses_total' => 'nullable|integer|min:1',
            'max_uses_per_member' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date',
            'is_active' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
            'is_single_use' => 'nullable|boolean',
        ];

        $payload = $request->all();

        if ($error = $this->rejectDeprecatedFields($payload, [
            'discount_type' => 'type',
            'discount_value' => 'value',
            'max_uses' => 'max_uses_total',
            'issue_date' => 'valid_from',
            'expiration_date' => 'valid_until',
        ])) {
            return $error;
        }

        $validator = Validator::make($payload, $rules);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray());
        }

        $updateData = array_filter($validator->validated(), fn ($v) => $v !== null);
        $updateData['updated_by'] = $partner->id;

        $voucher->update($updateData);

        return $this->jsonResource($voucher->fresh());
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:vouchers')) {
            return $denied;
        }

        $partner = $this->getPartner($request);
        $voucher = Voucher::where('id', $id)
            ->where('created_by', $partner->id)
            ->first();

        if (! $voucher) {
            return $this->jsonNotFound('Voucher');
        }

        $voucher->update(['deleted_by' => $partner->id]);
        $voucher->delete();

        return $this->jsonSuccess(['message' => 'Voucher deleted.']);
    }

    // ═══════════════════════════════════════════════════════════════════
    // VOUCHER OPERATIONS (via VoucherService)
    // ═══════════════════════════════════════════════════════════════════

    /**
     * POST /api/agent/v1/partner/vouchers/validate
     * Scope: write:vouchers
     *
     * Validate a voucher code without redeeming it.
     * Delegates to VoucherService::validate() for comprehensive checks.
     */
    public function validateVoucher(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:vouchers')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'member_identifier' => 'required|string',
            'club_id' => 'required|uuid',
            'order_amount' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray());
        }

        $club = $this->resolveClub($partner, $request->input('club_id'));
        if ($club instanceof JsonResponse) {
            return $club;
        }

        $member = $this->resolveMember($request->input('member_identifier'));
        if (! $member) {
            return $this->jsonNotFound('Member');
        }

        $result = $this->voucherService->validate(
            code: $request->input('code'),
            member: $member,
            clubId: $club->id,
            orderAmount: $request->input('order_amount') ? (int) $request->input('order_amount') : null,
        );

        if (! $result['valid']) {
            return $this->jsonError(
                code: 'VOUCHER_INVALID',
                message: $result['error_message'],
                status: 422,
                retryStrategy: 'no_retry',
                details: ['error_code' => $result['error_code']],
            );
        }

        $voucher = $result['voucher'];

        return $this->jsonSuccess([
            'data' => [
                'valid' => true,
                'voucher_id' => $voucher->id,
                'code' => $voucher->code,
                'name' => $voucher->name,
                'type' => $voucher->type,
                'value' => $voucher->value,
                'currency' => $voucher->currency,
                'discount_amount' => $result['discount_amount'],
                'capped' => $result['capped'] ?? false,
                'original_amount' => $result['original_amount'],
                'final_amount' => $result['final_amount'],
                'times_used' => $voucher->times_used,
                'remaining_uses' => $voucher->remaining_uses,
                'valid_until' => $voucher->valid_until?->toIso8601String(),
            ],
        ]);
    }

    /**
     * POST /api/agent/v1/partner/vouchers/{id}/redeem
     * Scope: write:vouchers
     *
     * Redeem a voucher for a member.
     * Delegates to VoucherService::redeem() for transactional redemption
     * with locking, audit trail, and event dispatching.
     */
    public function redeemVoucher(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:vouchers')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        $voucher = Voucher::where('id', $id)
            ->where('created_by', $partner->id)
            ->first();

        if (! $voucher) {
            return $this->jsonNotFound('Voucher');
        }

        $validator = Validator::make($request->all(), [
            'member_identifier' => 'required|string',
            'order_amount' => 'nullable|integer|min:0',
            'order_reference' => 'nullable|string|max:64',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray());
        }

        $member = $this->resolveMember($request->input('member_identifier'));
        if (! $member) {
            return $this->jsonNotFound('Member');
        }

        try {
            $result = $this->voucherService->redeem(
                voucher: $voucher,
                member: $member,
                orderAmount: $request->input('order_amount') ? (int) $request->input('order_amount') : null,
                orderReference: $request->input('order_reference'),
            );

            if (! $result['success']) {
                return $this->jsonError(
                    code: 'VOUCHER_REDEEM_FAILED',
                    message: $result['error'] ?? 'Unable to redeem voucher.',
                    status: 422,
                );
            }

            return $this->jsonSuccess([
                'data' => [
                    'voucher_id' => $voucher->id,
                    'code' => $voucher->code,
                    'type' => $voucher->type,
                    'member_id' => $member->id,
                    'discount_amount' => $result['discount_amount'],
                    'points_awarded' => $result['points_awarded'],
                    'remaining_uses' => $result['voucher_remaining_uses'],
                    'redemption_id' => $result['redemption']?->id,
                ],
            ]);
        } catch (\Exception $e) {
            report($e);

            return $this->jsonError(
                code: 'INTERNAL_ERROR',
                message: 'Unable to process voucher redemption.',
                status: 500,
                retryStrategy: 'retry_later',
            );
        }
    }
}
