<?php

declare(strict_types=1);

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

/**
 * Account Controller
 *
 * Handles account-level operations for members, including:
 * - Account switching (linking device to different member account)
 *
 * Account switching allows users to enter a member code and link their
 * current device to that account. This is particularly useful for:
 * - Anonymous members accessing their account from a new device
 * - Members switching between accounts on shared devices
 *
 * @package App\Http\Controllers\Member
 */
class AccountController extends Controller
{
    /**
     * Switch the current device to a different member account.
     *
     * Validates the provided member code, authenticates the user as that member,
     * and updates the device_uuid cookie to link this device to the new account.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function switch(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'max:20'],
        ]);

        $code = strtoupper(trim($request->input('code')));
        $currentMember = auth('member')->user();

        // Prevent switching to the same account
        if ($currentMember && strtoupper($currentMember->device_code) === $code) {
            return response()->json([
                'success' => false,
                'message' => trans('common.switch_account.same_account_error'),
            ], 400);
        }

        // Find the target member by their device code (e.g., "NXHYGH")
        $targetMember = Member::findByDeviceCode($code);

        if (!$targetMember) {
            return response()->json([
                'success' => false,
                'message' => trans('common.switch_account.code_not_found'),
            ], 404);
        }

        // Check if target member is active
        if (!$targetMember->is_active) {
            return response()->json([
                'success' => false,
                'message' => trans('common.switch_account.account_inactive'),
            ], 403);
        }

        // Log out the current member session
        Auth::guard('member')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Log in as the target member
        Auth::guard('member')->login($targetMember);

        // Get or generate the device UUID for the new member
        $deviceUuid = $targetMember->device_uuid;
        
        if (!$deviceUuid) {
            // Generate a new device UUID if the member doesn't have one
            $deviceUuid = \Illuminate\Support\Str::uuid()->toString();
            $targetMember->update(['device_uuid' => $deviceUuid]);
        }

        // Queue the cookie to be set (1 year expiry)
        Cookie::queue('member_device_uuid', $deviceUuid, 60 * 24 * 365, '/', null, false, false);

        return response()->json([
            'success' => true,
            'message' => trans('common.switch_account.success', ['code' => $targetMember->device_code]),
            'device_uuid' => $deviceUuid,
            'member_id' => $targetMember->id,
            'member_code' => $targetMember->device_code,
        ]);
    }
}
