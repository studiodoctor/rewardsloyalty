<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Purpose:
 * Unit tests for StampService - the core business logic for stamp cards.
 * Tests all critical operations including earning, redeeming, expiring stamps.
 */

use App\Events\StampCardCompleted;
use App\Events\StampEarned;
use App\Events\StampRewardRedeemed;
use App\Models\Club;
use App\Models\Member;
use App\Models\Staff;
use App\Models\StampCard;
use App\Services\StampService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->stampService = app(StampService::class);

    // Create test data
    $this->club = Club::factory()->create();
    $this->member = Member::factory()->create(['club_id' => $this->club->id]);
    $this->staff = Staff::factory()->create(['club_id' => $this->club->id]);

    $this->stampCard = StampCard::factory()->create([
        'club_id' => $this->club->id,
        'stamps_required' => 10,
        'stamps_per_purchase' => 1,
        'is_active' => true,
        'is_visible' => true,
        'is_auto_enroll' => true,
    ]);
});

it('adds stamps to a member card', function () {
    Event::fake([StampEarned::class]);

    $result = $this->stampService->addStamps(
        card: $this->stampCard,
        member: $this->member,
        stamps: 1,
        staff: $this->staff
    );

    expect($result['success'])->toBeTrue()
        ->and($result['stamps_added'])->toBe(1)
        ->and($result['current_total'])->toBe(1)
        ->and($result['completed'])->toBeFalse();

    Event::assertDispatched(StampEarned::class);
});

it('completes a stamp card when required stamps are reached', function () {
    Event::fake([StampCardCompleted::class]);

    // Add 9 stamps first
    $this->stampService->addStamps(
        card: $this->stampCard,
        member: $this->member,
        stamps: 9,
        staff: $this->staff
    );

    // Add the final stamp
    $result = $this->stampService->addStamps(
        card: $this->stampCard,
        member: $this->member,
        stamps: 1,
        staff: $this->staff
    );

    expect($result['success'])->toBeTrue()
        ->and($result['completed'])->toBeTrue()
        ->and($result['pending_rewards'])->toBe(1);

    Event::assertDispatched(StampCardCompleted::class);
});

it('redeems a pending reward', function () {
    Event::fake([StampRewardRedeemed::class]);

    // Complete a card first
    $this->stampService->addStamps(
        card: $this->stampCard,
        member: $this->member,
        stamps: 10,
        staff: $this->staff
    );

    // Redeem the reward
    $result = $this->stampService->redeemReward(
        card: $this->stampCard,
        member: $this->member,
        staff: $this->staff
    );

    expect($result['success'])->toBeTrue()
        ->and($result['reward_title'])->toBe($this->stampCard->reward_title)
        ->and($result['remaining_rewards'])->toBe(0);

    Event::assertDispatched(StampRewardRedeemed::class);
});

it('prevents redemption without staff when required', function () {
    // Complete a card first
    $this->stampService->addStamps(
        card: $this->stampCard,
        member: $this->member,
        stamps: 10,
        staff: $this->staff
    );

    // Try to redeem without staff
    $result = $this->stampService->redeemReward(
        card: $this->stampCard,
        member: $this->member,
        staff: null
    );

    expect($result['success'])->toBeFalse()
        ->and($result['error'])->toContain('Staff confirmation required');
});

it('respects daily stamp limits', function () {
    $this->stampCard->update(['max_stamps_per_day' => 2]);

    // Add 2 stamps
    $this->stampService->addStamps(
        card: $this->stampCard,
        member: $this->member,
        stamps: 2,
        staff: $this->staff
    );

    // Try to add more
    $result = $this->stampService->addStamps(
        card: $this->stampCard,
        member: $this->member,
        stamps: 1,
        staff: $this->staff
    );

    expect($result['success'])->toBeFalse()
        ->and($result['error'])->toContain('daily limit');
});

it('respects transaction stamp limits', function () {
    $this->stampCard->update(['max_stamps_per_transaction' => 2]);

    $result = $this->stampService->addStamps(
        card: $this->stampCard,
        member: $this->member,
        stamps: 5, // Attempt to add more than limit
        staff: $this->staff
    );

    // Should only add 2 stamps
    expect($result['success'])->toBeTrue()
        ->and($result['stamps_added'])->toBeLessThanOrEqual(2);
});

it('enrolls member automatically on first stamp', function () {
    expect($this->member->stampCardMembers()->count())->toBe(0);

    $this->stampService->addStamps(
        card: $this->stampCard,
        member: $this->member,
        stamps: 1,
        staff: $this->staff
    );

    expect($this->member->stampCardMembers()->count())->toBe(1);
});

it('checks earning eligibility correctly', function () {
    $eligibility = $this->stampService->checkEarningEligibility(
        card: $this->stampCard,
        member: $this->member
    );

    expect($eligibility['eligible'])->toBeTrue()
        ->and($eligibility['stamps_available'])->toBeGreaterThan(0);
});

it('prevents earning on inactive cards', function () {
    $this->stampCard->update(['is_active' => false]);

    $eligibility = $this->stampService->checkEarningEligibility(
        card: $this->stampCard,
        member: $this->member
    );

    expect($eligibility['eligible'])->toBeFalse()
        ->and($eligibility['reason'])->toContain('not currently available');
});

it('prevents earning below minimum purchase amount', function () {
    $this->stampCard->update(['min_purchase_amount' => 1000]); // $10.00

    $eligibility = $this->stampService->checkEarningEligibility(
        card: $this->stampCard,
        member: $this->member,
        purchaseAmount: 500 // $5.00
    );

    expect($eligibility['eligible'])->toBeFalse()
        ->and($eligibility['reason'])->toContain('minimum requirement');
});

it('can adjust stamps (add or subtract)', function () {
    // Add initial stamps
    $this->stampService->addStamps(
        card: $this->stampCard,
        member: $this->member,
        stamps: 5,
        staff: $this->staff
    );

    // Adjust (add 3)
    $result = $this->stampService->adjustStamps(
        card: $this->stampCard,
        member: $this->member,
        stamps: 3,
        reason: 'Bonus stamps',
        staff: $this->staff
    );

    expect($result['success'])->toBeTrue()
        ->and($result['new_total'])->toBe(8);

    // Adjust (subtract 2)
    $result = $this->stampService->adjustStamps(
        card: $this->stampCard,
        member: $this->member,
        stamps: -2,
        reason: 'Correction',
        staff: $this->staff
    );

    expect($result['success'])->toBeTrue()
        ->and($result['new_total'])->toBe(6);
});

it('returns accurate member statistics', function () {
    // Create another card and add stamps
    $secondCard = StampCard::factory()->create([
        'club_id' => $this->club->id,
        'stamps_required' => 5,
    ]);

    $this->stampService->addStamps(
        card: $this->stampCard,
        member: $this->member,
        stamps: 3,
        staff: $this->staff
    );

    $this->stampService->addStamps(
        card: $secondCard,
        member: $this->member,
        stamps: 2,
        staff: $this->staff
    );

    $stats = $this->stampService->getMemberStats($this->member);

    expect($stats['total_cards'])->toBe(2)
        ->and($stats['total_stamps'])->toBe(5)
        ->and($stats['cards'])->toHaveCount(2);
});

it('returns accurate card statistics', function () {
    // Create multiple members and add stamps
    $member2 = Member::factory()->create(['club_id' => $this->club->id]);

    $this->stampService->addStamps(
        card: $this->stampCard,
        member: $this->member,
        stamps: 10,
        staff: $this->staff
    );

    $this->stampService->addStamps(
        card: $this->stampCard,
        member: $member2,
        stamps: 5,
        staff: $this->staff
    );

    $stats = $this->stampService->getCardStatistics($this->stampCard);

    expect($stats['enrollment_count'])->toBe(2)
        ->and($stats['total_stamps_issued'])->toBe(15)
        ->and($stats['total_completions'])->toBeGreaterThanOrEqual(1);
});
