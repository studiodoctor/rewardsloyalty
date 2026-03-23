<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Purpose:
 * Seeds demo stamp card transactions and analytics with realistic data.
 * Uses same design approach as loyalty cards for consistency.
 */

namespace Database\Seeders;

use App\Models\Analytic;
use App\Models\Member;
use App\Models\Staff;
use App\Models\StampCard;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StampCardTransactionsAndAnalyticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stampService = app(\App\Services\StampService::class);

        $demoStaff = Staff::query()->where('email', 'staff@example.com')->first();

        $stampCardsQuery = StampCard::query()->with('club.partner.staff');
        if ($demoStaff) {
            $stampCardsQuery->where('created_by', $demoStaff->created_by)
                ->where('club_id', $demoStaff->club_id);
        }

        $stampCards = $stampCardsQuery->get();
        $members = Member::all();

        $this->command->info("Found {$stampCards->count()} stamp cards and {$members->count()} members");

        if ($stampCards->isEmpty()) {
            $this->command->warn('No stamp cards found. Skipping stamp card transactions seeder.');

            return;
        }

        if ($members->isEmpty()) {
            $this->command->warn('No members found. Skipping stamp card transactions seeder.');

            return;
        }

        $totalTransactions = 0;
        $totalViews = 0;

        // Each member interacts with only 2-3 random stamp cards (not all cards!)
        foreach ($members as $member) {
            // Randomly select 2-3 stamp cards for this member to interact with (or fewer if not enough cards)
            $numCards = min(mt_rand(2, 3), $stampCards->count());
            $cardsToInteract = $stampCards->random($numCards);

            foreach ($cardsToInteract as $stampCard) {
                // Stamp card partner (accessed through club)
                $partner = $stampCard->club?->partner;

                // Skip if no partner or partner has no staff
                if (! $partner) {
                    $this->command->warn("Stamp card {$stampCard->name} has no partner. Skipping.");

                    continue;
                }

                if ($partner->staff->isEmpty()) {
                    $this->command->warn("Partner {$partner->name} has no staff. Skipping.");

                    continue;
                }

                $staff = $demoStaff ?: $partner->staff()->inRandomOrder()->first();
                if (! $staff) {
                    continue;
                }

                // First a member visits a stamp card
                $startDate = fake()->dateTimeBetween('-120 days', '-120 days')->format('Y-m-d H:i:s');
                $endDate = Carbon::now();

                // Stamp card visits - 15-25 views spread over 120 days
                $visits = mt_rand(15, 25);
                for ($i = 0; $i < $visits; $i++) {
                    $interactionDate = fake()->dateTimeBetween($startDate, $endDate)->format('Y-m-d H:i:s');
                    Analytic::create([
                        'partner_id' => $partner->id,
                        'member_id' => $member->id,
                        'staff_id' => null,
                        'card_id' => null,
                        'reward_id' => null,
                        'stamp_card_id' => $stampCard->id,
                        'event' => 'stamp_card_view',
                        'locale' => $member->locale,
                        'created_at' => $interactionDate,
                    ]);
                    $stampCard->increment('views');
                    $stampCard->where('id', $stampCard->id)->update(['last_view' => Carbon::now('UTC')]);
                    $totalViews++;
                }

                // Add RECENT views (last 7 days) so charts show with "This Week" range
                $recentViews = mt_rand(5, 10);
                for ($i = 0; $i < $recentViews; $i++) {
                    $recentDate = fake()->dateTimeBetween('-7 days', 'now')->format('Y-m-d H:i:s');
                    Analytic::create([
                        'partner_id' => $partner->id,
                        'member_id' => $member->id,
                        'staff_id' => null,
                        'card_id' => null,
                        'reward_id' => null,
                        'stamp_card_id' => $stampCard->id,
                        'event' => 'stamp_card_view',
                        'locale' => $member->locale,
                        'created_at' => $recentDate,
                    ]);
                    $stampCard->increment('views');
                    $stampCard->where('id', $stampCard->id)->update(['last_view' => Carbon::now('UTC')]);
                    $totalViews++;
                }

                // Add views for TODAY to keep analytics alive (even on Sundays!)
                $todayViews = mt_rand(2, 4);
                for ($i = 0; $i < $todayViews; $i++) {
                    $todayDate = Carbon::now()->subHours(mt_rand(1, 10))->format('Y-m-d H:i:s');
                    Analytic::create([
                        'partner_id' => $partner->id,
                        'member_id' => $member->id,
                        'staff_id' => null,
                        'card_id' => null,
                        'reward_id' => null,
                        'stamp_card_id' => $stampCard->id,
                        'event' => 'stamp_card_view',
                        'locale' => $member->locale,
                        'created_at' => $todayDate,
                    ]);
                    $stampCard->increment('views');
                    $stampCard->where('id', $stampCard->id)->update(['last_view' => Carbon::now('UTC')]);
                    $totalViews++;
                }

                // Then a member earns stamps - 5-8 interactions
                $startDate = fake()->dateTimeBetween('-120 days', '-100 days')->format('Y-m-d H:i:s');
                $endDate = fake()->dateTimeBetween('-7 days', '-7 days')->format('Y-m-d H:i:s');

                $interactions = mt_rand(5, 8);
                for ($i = 0; $i < $interactions; $i++) {
                    // Define the range and step for purchase amounts
                    $min = 1.5;
                    $max = 200;
                    $step = 1.5;

                    // Calculate the number of steps in the range
                    $minSteps = (int) ceil($min / $step);
                    $maxSteps = (int) floor($max / $step);

                    // Generate a random number of steps within the range
                    $steps = rand($minSteps, $maxSteps);

                    // Calculate the actual random number
                    $purchase_amount = $steps * $step;

                    $interactionDate = ($i == 0) ? $startDate : fake()->dateTimeBetween($startDate, $endDate)->format('Y-m-d H:i:s');

                    // Check if purchase meets minimum requirement
                    if ($stampCard->min_purchase_amount === null || $purchase_amount >= $stampCard->min_purchase_amount) {
                        // Add stamp using the StampService
                        $result = $stampService->addStamp(
                            card: $stampCard,
                            member: $member,
                            staff: $staff,
                            stamps: 1,
                            purchaseAmount: $purchase_amount,
                            note: null,
                            createdAt: $interactionDate
                        );

                        if ($result['success']) {
                            $totalTransactions++;
                        }
                    }
                }

                // Then a member earns some more stamps closer to today (for analytics) - 2-4 interactions
                $startDate = fake()->dateTimeBetween('-7 days', '-7 days')->format('Y-m-d H:i:s');
                $endDate = fake()->dateTimeBetween('-1 days', '-1 days')->format('Y-m-d H:i:s');

                $interactions = mt_rand(2, 4);
                for ($i = 0; $i < $interactions; $i++) {
                    // Define the range and step for purchase amounts
                    $min = 1.5;
                    $max = 100;
                    $step = 1.5;

                    // Calculate the number of steps in the range
                    $minSteps = (int) ceil($min / $step);
                    $maxSteps = (int) floor($max / $step);

                    // Generate a random number of steps within the range
                    $steps = rand($minSteps, $maxSteps);

                    // Calculate the actual random number
                    $purchase_amount = $steps * $step;

                    $interactionDate = ($i == 0) ? $startDate : fake()->dateTimeBetween($startDate, $endDate)->format('Y-m-d H:i:s');

                    // Check if purchase meets minimum requirement
                    if ($stampCard->min_purchase_amount === null || $purchase_amount >= $stampCard->min_purchase_amount) {
                        // Add stamp using the StampService
                        $result = $stampService->addStamp(
                            card: $stampCard,
                            member: $member,
                            staff: $staff,
                            stamps: 1,
                            purchaseAmount: $purchase_amount,
                            note: null,
                            createdAt: $interactionDate
                        );

                        if ($result['success']) {
                            $totalTransactions++;
                        }
                    }
                }

                // Finally a member claims some rewards - 1-2 redemptions
                // Only redeem if member has pending rewards
                $enrollment = $stampService->getMemberProgress($stampCard, $member);

                if ($enrollment && $enrollment->pending_rewards > 0) {
                    $startDate = fake()->dateTimeBetween('-80 days', '-2 days')->format('Y-m-d H:i:s');
                    $endDate = Carbon::now();

                    $redemptions = min(mt_rand(1, 2), $enrollment->pending_rewards);
                    for ($i = 0; $i < $redemptions; $i++) {
                        $interactionDate = fake()->dateTimeBetween($startDate, $endDate)->format('Y-m-d H:i:s');

                        // Redeem reward using the StampService
                        $stampService->redeemReward(
                            card: $stampCard,
                            member: $member,
                            staff: $staff,
                            createdAt: $interactionDate
                        );
                    }
                }
            }
        }

        $this->command->info('Stamp card transactions seeded successfully!');
        $this->command->info("Total views created: {$totalViews}");
        $this->command->info("Total transactions created: {$totalTransactions}");
    }
}
