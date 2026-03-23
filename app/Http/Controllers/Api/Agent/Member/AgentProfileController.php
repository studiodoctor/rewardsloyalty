<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Agent API: Member self-service profile management.
 *
 * Returns the authenticated member's own profile data and allows
 * limited updates (name, locale). Sensitive fields like email
 * cannot be changed via the agent API — that requires the full
 * authentication flow via the member dashboard.
 *
 * Scopes:
 *   read              → GET /profile
 *   write:profile     → PUT /profile
 *
 * @see RewardLoyalty-100d-phase4-advanced.md §2.4
 */

namespace App\Http\Controllers\Api\Agent\Member;

use App\Http\Controllers\Api\Agent\BaseAgentController;
use App\Http\Controllers\Api\Agent\Concerns\EnforcesMemberGates;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AgentProfileController extends BaseAgentController
{
    use EnforcesMemberGates;

    /**
     * GET /api/agent/v1/member/profile
     * Scope: read
     *
     * Returns the member's own profile — name, email, locale,
     * avatar, member number, and interaction status.
     */
    public function show(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'read')) {
            return $denied;
        }

        return $this->jsonSuccess([
            'data' => $this->serializeMemberProfile($this->getMember($request), includeStatus: true),
        ]);
    }

    /**
     * PUT /api/agent/v1/member/profile
     * Scope: write:profile
     *
     * Update limited profile fields. Members cannot change their
     * email via API — that requires the full auth flow.
     */
    public function update(Request $request): JsonResponse
    {
        if ($denied = $this->requireScope($request, 'write:profile')) {
            return $denied;
        }

        $payload = $request->all();

        if (array_key_exists('locale', $payload) && is_string($payload['locale'])) {
            $payload['locale'] = $this->normalizeLocale($payload['locale']);
        }

        $validator = Validator::make($payload, [
            'name' => 'sometimes|string|max:200',
            'locale' => ['sometimes', 'string', 'size:5', Rule::in($this->installedLocales())],
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray());
        }

        $member = $this->getMember($request);

        $fields = $validator->validated();

        if (! empty($fields)) {
            $member->update($fields);
        }

        return $this->jsonSuccess([
            'data' => $this->serializeMemberProfile($member),
        ]);
    }

    /**
     * Normalize locale input to the platform's stored format, e.g. fr_FR.
     */
    private function normalizeLocale(string $locale): string
    {
        $locale = str_replace('-', '_', trim($locale));

        if (preg_match('/^[A-Za-z]{2}_[A-Za-z]{2}$/', $locale) !== 1) {
            return $locale;
        }

        [$language, $region] = explode('_', $locale, 2);

        return strtolower($language) . '_' . strtoupper($region);
    }

    /**
     * Installed locale codes from the lang directory.
     *
     * @return array<int, string>
     */
    private function installedLocales(): array
    {
        static $locales = null;

        if ($locales === null) {
            $locales = collect(File::directories(lang_path()))
                ->map(fn (string $path) => basename($path))
                ->sort()
                ->values()
                ->all();
        }

        return $locales;
    }

    /**
     * Serialize the member's self-service profile fields.
     *
     * Club relationships and unused display aliases are intentionally
     * excluded from the member contract.
     *
     * @return array<string, mixed>
     */
    private function serializeMemberProfile(Member $member, bool $includeStatus = false): array
    {
        $profile = [
            'id' => $member->id,
            'name' => $member->name,
            'email' => $member->email,
            'locale' => $member->locale,
            'unique_identifier' => $member->unique_identifier,
            'avatar' => $member->avatar,
        ];

        if (! $includeStatus) {
            return $profile;
        }

        $profile['is_anonymous'] = $member->isAnonymous();
        $profile['has_interacted'] = $member->hasInteracted();
        $profile['first_interaction_at'] = $member->first_interaction_at?->toIso8601String();
        $profile['created_at'] = $member->created_at->toIso8601String();

        return $profile;
    }
}
