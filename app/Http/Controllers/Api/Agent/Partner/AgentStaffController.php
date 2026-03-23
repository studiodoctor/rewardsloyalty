<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Agent API: Staff management for partners.
 * Staff members operate loyalty cards (scan, award, redeem).
 *
 * @see RewardLoyalty-100b-phase2-core-endpoints.md §2.9
 */

namespace App\Http\Controllers\Api\Agent\Partner;

use App\Http\Controllers\Api\Agent\BaseAgentController;
use App\Http\Controllers\Api\Agent\Concerns\EnforcesPartnerGates;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AgentStaffController extends BaseAgentController
{
    use EnforcesPartnerGates;

    public function index(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read', 'write:staff')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        $staff = Staff::where('created_by', $partner->id)
            ->with('club:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate($this->getPerPage());

        return $this->jsonSuccess([
            'data' => $staff->getCollection()->map(
                fn (Staff $staffMember) => $this->serializePartnerStaff($staffMember)
            )->values(),
            'pagination' => $this->paginationMeta($staff),
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read', 'write:staff')) {
            return $denied;
        }

        $partner = $this->getPartner($request);
        $staff = Staff::where('id', $id)
            ->where('created_by', $partner->id)
            ->with('club:id,name')
            ->first();

        if (! $staff) {
            return $this->jsonNotFound('Staff member');
        }

        return $this->jsonSuccess([
            'data' => $this->serializePartnerStaff($staff),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:staff')) {
            return $denied;
        }

        $partner = $this->getPartner($request);

        if ($error = $this->checkLimit($partner, 'staff_members_limit', Staff::class, 'Staff members')) {
            return $error;
        }

        $payload = $request->all();

        if ($error = $this->rejectDeprecatedFields($payload, [
            'club_ids' => 'club_id',
        ])) {
            return $error;
        }

        $validator = Validator::make($payload, [
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:120|unique:staff,email',
            'password' => 'required|string|min:6|max:48',
            'club_id' => 'nullable|uuid',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray());
        }

        // Validate club belongs to partner if provided
        $clubId = $validator->validated()['club_id'] ?? null;
        if ($clubId) {
            $club = $this->resolveClub($partner, $clubId);
            if ($club instanceof JsonResponse) {
                return $club;
            }
        }

        $validated = $validator->validated();

        $staff = Staff::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'club_id' => $clubId,
            'is_active' => $validated['is_active'] ?? true,
            'created_by' => $partner->id,
        ]);

        return $this->jsonSuccess([
            'data' => $this->serializePartnerStaff($staff->load('club:id,name')),
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:staff')) {
            return $denied;
        }

        $partner = $this->getPartner($request);
        $staff = Staff::where('id', $id)
            ->where('created_by', $partner->id)
            ->first();

        if (! $staff) {
            return $this->jsonNotFound('Staff member');
        }

        $payload = $request->all();

        if ($error = $this->rejectDeprecatedFields($payload, [
            'club_ids' => 'club_id',
        ])) {
            return $error;
        }

        $validator = Validator::make($payload, [
            'name' => 'nullable|string|max:120',
            'email' => 'nullable|email|max:120|unique:staff,email,' . $staff->id,
            'password' => 'nullable|string|min:6|max:48',
            'club_id' => 'nullable|uuid',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray());
        }

        $validated = $validator->validated();

        $updateData = array_filter([
            'name' => $validated['name'] ?? null,
            'email' => $validated['email'] ?? null,
            'is_active' => $validated['is_active'] ?? null,
            'updated_by' => $partner->id,
        ], fn ($v) => $v !== null);

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        // Update club assignment if provided
        if (array_key_exists('club_id', $validated)) {
            $clubId = $validated['club_id'];
            if ($clubId) {
                $club = $this->resolveClub($partner, $clubId);
                if ($club instanceof JsonResponse) {
                    return $club;
                }
            }
            $updateData['club_id'] = $clubId;
        }

        $staff->update($updateData);

        return $this->jsonSuccess([
            'data' => $this->serializePartnerStaff($staff->fresh()->load('club:id,name')),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:staff')) {
            return $denied;
        }

        $partner = $this->getPartner($request);
        $staff = Staff::where('id', $id)
            ->where('created_by', $partner->id)
            ->first();

        if (! $staff) {
            return $this->jsonNotFound('Staff member');
        }

        $staff->delete();

        return $this->jsonSuccess(['message' => 'Staff member deleted.']);
    }

    private function serializePartnerStaff(Staff $staff): array
    {
        return [
            'id' => $staff->id,
            'club_id' => $staff->club_id,
            'club_name' => $staff->club?->name,
            'name' => $staff->name,
            'email' => $staff->email,
            'locale' => $staff->locale,
            'time_zone' => $staff->time_zone,
            'number_of_times_logged_in' => (int) $staff->number_of_times_logged_in,
            'last_login_at' => $staff->last_login_at
                ? Carbon::parse($staff->last_login_at)->toIso8601String()
                : null,
            'created_at' => $this->serializeDateTime($staff->created_at),
            'updated_at' => $this->serializeDateTime($staff->updated_at),
            'avatar' => $staff->avatar,
        ];
    }
}
