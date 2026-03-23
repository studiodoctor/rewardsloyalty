<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Purpose:
 * Feature tests for Member StampCardController - testing member stamp card views and API.
 */

use App\Models\Club;
use App\Models\Member;
use App\Models\Staff;
use App\Models\StampCard;
use App\Services\StampService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->club = Club::factory()->create();
    $this->member = Member::factory()->create(['club_id' => $this->club->id]);
    $this->staff = Staff::factory()->create(['club_id' => $this->club->id]);

    $this->stampCard = StampCard::factory()->create([
        'club_id' => $this->club->id,
        'stamps_required' => 10,
        'stamps_per_purchase' => 1,
        'is_active' => true,
        'is_visible' => true,
    ]);

    $this->actingAs($this->member, 'member');
});

it('returns all member stamp cards via API', function () {
    // Add some stamps to enroll member
    $stampService = app(StampService::class);
    $stampService->addStamps(
        card: $this->stampCard,
        member: $this->member,
        stamps: 3,
        staff: $this->staff
    );

    $response = $this->getJson('/api/en/v1/member/stamp-cards');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => [
                'stamp_cards' => [
                    '*' => [
                        'id',
                        'title',
                        'current_stamps',
                        'stamps_required',
                        'progress_percentage',
                        'reward_title',
                        'colors',
                    ],
                ],
                'stats' => [
                    'total_cards',
                    'total_stamps',
                ],
            ],
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'stamp_cards' => [
                    [
                        'id' => $this->stampCard->id,
                        'current_stamps' => 3,
                        'stamps_required' => 10,
                    ],
                ],
            ],
        ]);
});

it('returns stamp card transaction history when allowed', function () {
    // Enroll and add stamps
    $stampService = app(StampService::class);
    $stampService->addStamps(
        card: $this->stampCard,
        member: $this->member,
        stamps: 5,
        staff: $this->staff
    );

    $response = $this->getJson("/api/en/v1/member/stamp-cards/{$this->stampCard->id}/history");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => [
                'transactions' => [
                    '*' => [
                        'id',
                        'event',
                        'stamps',
                        'created_at',
                    ],
                ],
            ],
        ]);
});

it('denies history access when not allowed by card settings', function () {
    $this->stampCard->update(['allow_member_view_history' => false]);

    $response = $this->getJson("/api/en/v1/member/stamp-cards/{$this->stampCard->id}/history");

    $response->assertForbidden();
});

it('prevents member from accessing other clubs stamp cards', function () {
    $otherClub = Club::factory()->create();
    $otherCard = StampCard::factory()->create(['club_id' => $otherClub->id]);

    $response = $this->getJson("/api/en/v1/member/stamp-cards/{$otherCard->id}/history");

    $response->assertNotFound();
});

it('shows progress percentage correctly', function () {
    // Add 50% of required stamps
    $stampService = app(StampService::class);
    $stampService->addStamps(
        card: $this->stampCard,
        member: $this->member,
        stamps: 5,
        staff: $this->staff
    );

    $response = $this->getJson('/api/en/v1/member/stamp-cards');

    $response->assertSuccessful()
        ->assertJson([
            'data' => [
                'stamp_cards' => [
                    [
                        'id' => $this->stampCard->id,
                        'progress_percentage' => 50.0,
                    ],
                ],
            ],
        ]);
});

it('shows pending rewards after completion', function () {
    // Complete the card
    $stampService = app(StampService::class);
    $stampService->addStamps(
        card: $this->stampCard,
        member: $this->member,
        stamps: 10,
        staff: $this->staff
    );

    $response = $this->getJson('/api/en/v1/member/stamp-cards');

    $response->assertSuccessful()
        ->assertJson([
            'data' => [
                'stamp_cards' => [
                    [
                        'id' => $this->stampCard->id,
                        'pending_rewards' => 1,
                        'completed_count' => 1,
                    ],
                ],
            ],
        ]);
});
