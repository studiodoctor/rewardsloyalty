<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Purpose:
 * Feature tests for Staff StampController - testing stamp operations API.
 */

use App\Models\Club;
use App\Models\Member;
use App\Models\Staff;
use App\Models\StampCard;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->club = Club::factory()->create();
    $this->staff = Staff::factory()->create(['club_id' => $this->club->id]);
    $this->member = Member::factory()->create(['club_id' => $this->club->id]);

    $this->stampCard = StampCard::factory()->create([
        'club_id' => $this->club->id,
        'stamps_required' => 10,
        'stamps_per_purchase' => 1,
        'is_active' => true,
    ]);

    $this->actingAs($this->staff, 'staff');
});

it('allows staff to add stamps to member card', function () {
    $response = $this->postJson(route('staff.stamps.add'), [
        'member_identifier' => $this->member->unique_identifier,
        'stamp_card_id' => $this->stampCard->id,
        'purchase_amount' => 15.50,
        'notes' => 'Regular purchase',
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'data' => [
                'stamps_added' => 1,
                'current_total' => 1,
                'completed' => false,
            ],
        ]);
});

it('allows staff to redeem rewards', function () {
    // First, complete a card
    $this->postJson(route('staff.stamps.add'), [
        'member_identifier' => $this->member->unique_identifier,
        'stamp_card_id' => $this->stampCard->id,
    ])->repeat(10);

    // Then redeem
    $response = $this->postJson(route('staff.stamps.redeem'), [
        'member_identifier' => $this->member->unique_identifier,
        'stamp_card_id' => $this->stampCard->id,
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
        ]);
});

it('prevents staff from accessing other clubs cards', function () {
    $otherClub = Club::factory()->create();
    $otherCard = StampCard::factory()->create(['club_id' => $otherClub->id]);

    $response = $this->postJson(route('staff.stamps.add'), [
        'member_identifier' => $this->member->unique_identifier,
        'stamp_card_id' => $otherCard->id,
    ]);

    $response->assertForbidden();
});

it('validates required fields for adding stamps', function () {
    $response = $this->postJson(route('staff.stamps.add'), [
        // Missing required fields
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['member_identifier', 'stamp_card_id']);
});

it('returns member stamp card status', function () {
    // Add some stamps first
    $this->postJson(route('staff.stamps.add'), [
        'member_identifier' => $this->member->unique_identifier,
        'stamp_card_id' => $this->stampCard->id,
    ]);

    $response = $this->getJson(route('staff.stamps.member', [
        'identifier' => $this->member->unique_identifier,
    ]));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => [
                'member' => ['id', 'name', 'identifier'],
                'stamp_cards' => [
                    '*' => [
                        'id',
                        'title',
                        'current_stamps',
                        'stamps_required',
                        'progress_percentage',
                    ],
                ],
            ],
        ]);
});

it('handles invalid member identifier gracefully', function () {
    $response = $this->postJson(route('staff.stamps.add'), [
        'member_identifier' => 'invalid-identifier',
        'stamp_card_id' => $this->stampCard->id,
    ]);

    $response->assertNotFound();
});
