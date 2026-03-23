<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 */

namespace Database\Seeders;

use App\Models\Analytic;
use App\Models\Card;
use App\Models\Member;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransactionsAndAnalyticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $analyticsService = app(\App\Services\AnalyticsService::class);
        $transactionService = app(\App\Services\Card\TransactionService::class);

        $demoStaff = Staff::query()->where('email', 'staff@example.com')->first();

        $cardsQuery = Card::query()->with(['partner.staff']);
        if ($demoStaff) {
            // Force demo interactions to be performed by staff@example.com (must be related to card).
            $cardsQuery->where('created_by', $demoStaff->created_by)
                ->where('club_id', $demoStaff->club_id);
        }

        $cards = $cardsQuery->get();
        $members = Member::all();

        if ($demoStaff && $cards->isEmpty()) {
            $this->command?->warn('No cards found for staff@example.com. Falling back to any cards.');
            $cards = Card::query()->with(['partner.staff'])->get();
        }

        // Each member interacts with only 2-3 random cards (not all cards!)
        foreach ($members as $member) {
            // Randomly select 2-3 cards for this member to interact with
            $cardsToInteract = $cards->random(mt_rand(2, 3));

            foreach ($cardsToInteract as $card) {
                // Card partner
                $partner = $card->getRelation('partner');
                if (! $partner) {
                    continue;
                }

                $staff = $demoStaff;
                if (! $staff) {
                    $staff = $partner->staff()->inRandomOrder()->first();
                }
                if (! $staff) {
                    continue;
                }

                // First a member visits a card and rewards
                $startDate = fake()->dateTimeBetween('-120 days', '-120 days')->format('Y-m-d H:i:s');
                $endDate = Carbon::now();

                // Card visits - REDUCED from 45-60 to 15-25
                $visits = mt_rand(15, 25);
                for ($i = 0; $i < $visits; $i++) {
                    $interactionDate = fake()->dateTimeBetween($startDate, $endDate)->format('Y-m-d H:i:s');
                    Analytic::create([
                        'partner_id' => $partner->id,
                        'member_id' => $member->id,
                        'staff_id' => null,
                        'card_id' => $card->id,
                        'reward_id' => null,
                        'event' => 'card_view',
                        'locale' => $member->locale,
                        'created_at' => $interactionDate,
                    ]);
                    $card->increment('views');
                    $card->where('id', $card->id)->update(['last_view' => Carbon::now('UTC')]);
                }

                // Reward visits - REDUCED from 65-85 to 20-30
                $visits = mt_rand(20, 30);
                for ($i = 0; $i < $visits; $i++) {
                    $rewards = $card->rewards()->inRandomOrder()->limit(4)->get();
                    foreach ($rewards as $reward) {
                        $interactionDate = fake()->dateTimeBetween($startDate, $endDate)->format('Y-m-d H:i:s');
                        Analytic::create([
                            'partner_id' => $partner->id,
                            'member_id' => $member->id,
                            'staff_id' => null,
                            'card_id' => $card->id,
                            'reward_id' => $reward->id,
                            'event' => 'reward_view',
                            'locale' => $member->locale,
                            'created_at' => $interactionDate,
                        ]);
                        $reward->increment('views');
                        $reward->where('id', $reward->id)->update(['last_view' => Carbon::now('UTC')]);
                    }
                }

                // Then a member earns points - REDUCED from 8-14 to 5-8
                $startDate = fake()->dateTimeBetween('-120 days', '-100 days')->format('Y-m-d H:i:s');
                $endDate = fake()->dateTimeBetween('-7 days', '-7 days')->format('Y-m-d H:i:s');

                $interactions = mt_rand(5, 8);
                for ($i = 0; $i < $interactions; $i++) {
                    // Define the range and step
                    $min = 1.5;
                    $max = 200;
                    $step = 1.5;

                    // Calculate the number of steps in the range
                    $minSteps = (int) ceil($min / $step);
                    $maxSteps = (int) floor($max / $step);

                    // Generate a random number of steps within the range
                    $steps = random_int($minSteps, $maxSteps);

                    // Calculate the actual random number
                    $purchase_amount = $steps * $step;

                    $interactionDate = ($i == 0) ? $startDate : fake()->dateTimeBetween($startDate, $endDate)->format('Y-m-d H:i:s');

                    $transactionService->addPurchase($member->unique_identifier, $card->unique_identifier, $staff, $purchase_amount, null, null, null, false, $interactionDate);
                }

                // Then a member earns some more points closer to today (for analytics) - REDUCED from 3-6 to 2-4
                $startDate = fake()->dateTimeBetween('-7 days', '-7 days')->format('Y-m-d H:i:s');
                $endDate = fake()->dateTimeBetween('-1 days', '-1 days')->format('Y-m-d H:i:s');

                $interactions = mt_rand(2, 4);
                for ($i = 0; $i < $interactions; $i++) {
                    // Define the range and step
                    $min = 1.5;
                    $max = 100;
                    $step = 1.5;

                    // Calculate the number of steps in the range
                    $minSteps = (int) ceil($min / $step);
                    $maxSteps = (int) floor($max / $step);

                    // Generate a random number of steps within the range
                    $steps = random_int($minSteps, $maxSteps);

                    // Calculate the actual random number
                    $purchase_amount = $steps * $step;

                    $interactionDate = ($i == 0) ? $startDate : fake()->dateTimeBetween($startDate, $endDate)->format('Y-m-d H:i:s');

                    $transactionService->addPurchase($member->unique_identifier, $card->unique_identifier, $staff, $purchase_amount, null, null, null, false, $interactionDate);
                }

                // Add MORE recent transactions for THIS MONTH to show growth (not decline!)
                // This creates an upward trend for better demo screenshots
                $currentMonth = Carbon::now()->startOfMonth();
                $yesterday = Carbon::now()->subDay();
                
                $recentInteractions = mt_rand(4, 8); // More transactions this month
                for ($i = 0; $i < $recentInteractions; $i++) {
                    // Define the range and step - varied amounts for realism
                    $min = 5;
                    $max = 250;
                    $step = 1.5;

                    // Calculate the number of steps in the range
                    $minSteps = (int) ceil($min / $step);
                    $maxSteps = (int) floor($max / $step);

                    // Generate a random number of steps within the range
                    $steps = random_int($minSteps, $maxSteps);

                    // Calculate the actual random number
                    $purchase_amount = $steps * $step;

                    // Spread transactions throughout the current month
                    $interactionDate = fake()->dateTimeBetween($currentMonth, $yesterday)->format('Y-m-d H:i:s');

                    $transactionService->addPurchase($member->unique_identifier, $card->unique_identifier, $staff, $purchase_amount, null, null, null, false, $interactionDate);
                }

                // Add transactions for TODAY to keep analytics alive (even on Sundays!)
                $todayInteractions = mt_rand(1, 2);
                for ($i = 0; $i < $todayInteractions; $i++) {
                    $min = 10;
                    $max = 150;
                    $step = 1.5;
                    
                    $minSteps = (int) ceil($min / $step);
                    $maxSteps = (int) floor($max / $step);
                    $steps = random_int($minSteps, $maxSteps);
                    $purchase_amount = $steps * $step;

                    // Use actual current timestamp for today
                    $todayDate = Carbon::now()->subHours(mt_rand(1, 8))->format('Y-m-d H:i:s');
                    $transactionService->addPurchase($member->unique_identifier, $card->unique_identifier, $staff, $purchase_amount, null, null, null, false, $todayDate);
                }

                // Finally a member claims some rewards - REDUCED from 2-4 to 1-2
                $startDate = fake()->dateTimeBetween('-80 days', '-2 days')->format('Y-m-d H:i:s');
                $endDate = \Carbon\Carbon::now();

                $interactions = mt_rand(1, 2);
                for ($i = 0; $i < $interactions; $i++) {
                    $interactionDate = fake()->dateTimeBetween($startDate, $endDate)->format('Y-m-d H:i:s');

                    $reward = $card->rewards()->inRandomOrder()->first();
                    if (! $reward) {
                        continue;
                    }

                    $transactionService->claimReward($card->id, $reward->id, $member->unique_identifier, $staff, null, null, $interactionDate);
                }
            }
        }
    }
}
