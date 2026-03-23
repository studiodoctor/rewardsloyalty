<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Agent API Health Endpoint
 *
 * Smoke-test endpoint for agent integrations. Returns key identity,
 * owner role, scopes, and rate limit information. Used by agents to
 * verify their key works, check remaining scopes, and confirm
 * connectivity before performing operations.
 *
 * This endpoint is available to all agent roles (admin, partner, member).
 *
 * @see RewardLoyalty-100-agent.md §10
 */

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Api\Agent\Concerns\ReturnsAgentErrors;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentHealthController extends BaseAgentController
{
    /**
     * GET /api/agent/v1/health
     *
     * Returns key identity and capability summary.
     * No scope requirement — any valid key can call this.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $agentKey = $request->attributes->get('agent_key');
        $owner = $request->attributes->get('agent_owner');

        return $this->jsonSuccess([
            'data' => [
                'status' => 'ok',
                'key' => [
                    'name' => $agentKey->name,
                    'prefix' => $agentKey->key_prefix,
                    'role' => $agentKey->getRoleName(),
                    'scopes' => $agentKey->scopes,
                    'rate_limit' => $agentKey->rate_limit,
                    'expires_at' => $agentKey->expires_at?->toIso8601String(),
                    'created_at' => $agentKey->created_at->toIso8601String(),
                    'last_used_at' => $agentKey->last_used_at?->toIso8601String(),
                ],
                'owner' => [
                    'id' => $owner->id,
                    'name' => $owner->name ?? $owner->email ?? null,
                    'type' => $agentKey->getRoleName(),
                ],
            ],
        ]);
    }
}
