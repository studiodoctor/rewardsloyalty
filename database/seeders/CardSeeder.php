<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Purpose:
 * Seeds demo loyalty card programs with realistic data.
 */

namespace Database\Seeders;

use App\Models\Card;
use App\Models\Partner;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check for seed-all-images mode (creates one card per image for testing backgrounds)
        $seedAllImages = env('APP_DEMO_SEED_ALL_IMAGES', false);

        $cardsPerType = 1;
        $rewardsPerType = 6; // Must match RewardSeeder's $rewardsPerType for correct slicing
        $rewardsPerCard = 4;
        // Loyalty cards work best for frequent, repeat-purchase businesses
        $businessTypes = ['restaurants', 'cafes', 'beauty', 'grocery'];

        // Max visible cards (one per business type for homepage variety)
        $maxVisibleByDefault = 3;

        foreach ($businessTypes as $businessTypeIndex => $businessType) {
            // Count demo images (skip if directory doesn't exist - e.g., on Forge deployments)
            $imageDirectory = database_path('data/demo-images/'.$businessType.'/cards/');
            $imageCount = 0;
            if (File::exists($imageDirectory)) {
                $files = File::files($imageDirectory);
                foreach ($files as $file) {
                    if ($file->getExtension() == 'jpg') {
                        $imageCount++;
                    }
                }
            }

            // Locales (skip if JSON directory doesn't exist)
            $directory = database_path('data/demo/'.$businessType.'/cards/');
            if (! File::exists($directory)) {
                continue;
            }
            $files = File::files($directory);

            $locales = array_map(function ($file) {
                return pathinfo($file->getFilename(), PATHINFO_FILENAME);
            }, $files);

            // Track used keys and images across ALL cards for this business type
            $usedKeys = [];
            $usedImageNumbers = [];

            // In seed-all-images mode, create one card per available image
            if ($seedAllImages && $imageCount > 0) {
                $cardsPerType = $imageCount;
            } else {
                $cardsPerType = 1;
            }

            // Load card content once for all iterations
            foreach ($locales as $locale) {
                $jsonFilePath = database_path('data/demo/'.$businessType.'/cards/'.$locale.'.json');
                $jsonString = file_get_contents($jsonFilePath);
                $cards[$locale] = json_decode($jsonString, true);
            }

            // Premium Colors - Rich, Luxurious Palette
            $colorValues = [
                '#0047AB', // 1. Deep Sapphire Blue (Rich, Corporate)
                '#B50031', // 2. Ferrari Red / Deep Scarlet (High contrast, Luxury Red)
                '#007A65', // 3. Muted Deep Teal (Sophisticated Green/Blue)
                '#C49A00', // 4. Rich Gold/Ochre (Deep, warm metal)
                '#7045AF', // 5. Deep Plum/Violet (Muted, elegant purple)
                '#202E44', // 6. Midnight Blue/Charcoal (Exclusive, high-end base)
                '#F26419', // 7. Rich Burnt Orange (Vibrant yet premium)
                '#3B82F6', // 8. Platform Accent Blue (Modern, clean)
                '#10B981', // 9. Vivid Emerald Green (Clean success color)
                '#DA291C', // 10. True Scarlet (Vibrant action color)
                '#A37A00', // 11. Deep Bronze (Earthy metal)
                '#E55D87', // 12. Dusty Rose (Elegant Pink)
            ];

            // Get the partners from the database
            $partners = Partner::all();

            for ($cardCount = 0; $cardCount < $cardsPerType; $cardCount++) {
                $colorCounter = ($businessTypeIndex == 0) ? 0 : $businessTypeIndex + 2;
                $colorCounter += $cardCount;

                foreach ($partners as $index => $partner) {

                    foreach ($partner->clubs as $club) {
                        for ($i = 0; $i < 1; $i++) {
                            // In seed-all-images mode, use fixed valid dates to ensure all cards are active
                            if ($seedAllImages) {
                                $created_at = now()->subMonths(2);
                                $issue_date = now()->subMonth();
                                $expiration_date = now()->addYears(5);
                            } else {
                                $created_at = fake()->dateTimeBetween('-14 month', '-4 month');
                                $issue_date = fake()->dateTimeBetween('-4 month', '-3 month');
                                $expiration_date = fake()->dateTimeBetween('+1 year', '+7 year');
                            }

                            // Color
                            // $bg_color = $values[array_rand($values)];
                            $bg_color = $colorValues[$colorCounter % count($colorValues)];
                            $colorCounter++;

                            // Numbers
                            $values = [10, 20, 50, 100, 100, 100, 100, 100, 150, 200, 250];
                            $initial_bonus_points = $values[array_rand($values)];

                            $values = [6, 8, 10, 11];
                            $points_expiration_months = $values[array_rand($values)];

                            $values = [1, 5, 10];
                            $currency_unit_amount = $values[array_rand($values)];

                            $values = [5, 10, 50];
                            $points_per_currency = $values[array_rand($values)] * $currency_unit_amount;

                            $values = [1, 10, 50, 100];
                            $min_points_per_purchase = $values[array_rand($values)];

                            $values = [10000, 50000, 100000, 1000000, 1000000, 1000000, 1000000];
                            $max_points_per_purchase = $values[array_rand($values)];

                            // Pick a unique card key, or reuse if exhausted
                            $availableCards = count($cards[$locales[0]]);
                            if (count($usedKeys) >= $availableCards) {
                                // All cards used, allow reuse
                                $randomKey = array_rand($cards[$locales[0]]);
                            } else {
                                do {
                                    $randomKey = array_rand($cards[$locales[0]]);
                                } while (isset($usedKeys[$randomKey]));
                            }
                            $usedKeys[$randomKey] = true;

                            foreach ($locales as $locale) {
                                /*
                                $faker = Factory::create($locale);
                                $head[$locale] = $faker->company();
                                $title[$locale] = $faker->sentence(4);
                                $description[$locale] = $faker->sentence(3);
                                */
                                $head[$locale] = $cards[$locale][$randomKey]['head'];
                                $title[$locale] = $cards[$locale][$randomKey]['title'];
                                $description[$locale] = $cards[$locale][$randomKey]['description'];
                            }

                            $is_active = true;
                            $is_visible_by_default = false;

                            // In seed-all-images mode, all cards are visible for testing
                            if ($seedAllImages) {
                                $is_visible_by_default = true;
                            }
                            // Make first card of each business type visible (for homepage variety)
                            // Only first 3 types get visibility, and only the first card per type
                            elseif ($club->name != 'Archived' && $cardCount == 0 && $businessTypeIndex < $maxVisibleByDefault) {
                                $is_visible_by_default = true;
                            }

                            $card = Card::create([
                                'club_id' => $club->id,
                                'name' => $cards['en_US'][$randomKey]['head'], // fake()->sentence(rand(1,3)),
                                'head' => $head,
                                'title' => $title,
                                'description' => $description,
                                'issue_date' => $issue_date,
                                'expiration_date' => $expiration_date,
                                'bg_color' => $bg_color, // fake()->hexcolor(),
                                'bg_color_opacity' => rand(79, 88),
                                'text_color' => '#ffffff',
                                'text_label_color' => '#ffffff',
                                'qr_color_light' => '#ffffff',
                                'qr_color_dark' => $bg_color, // '#333333',
                                'currency' => 'USD',
                                'initial_bonus_points' => $initial_bonus_points,
                                'points_expiration_months' => $points_expiration_months,
                                'currency_unit_amount' => $currency_unit_amount,
                                'points_per_currency' => $points_per_currency,
                                'point_value' => 0,
                                'min_points_per_purchase' => $min_points_per_purchase,
                                'max_points_per_purchase' => $max_points_per_purchase,
                                'min_points_per_redemption' => 0,
                                'max_points_per_redemption' => 0,
                                'is_active' => $is_active,
                                'is_undeletable' => env('APP_IS_UNEDITABLE', true),
                                'is_uneditable' => env('APP_IS_UNEDITABLE', true),
                                'is_visible_by_default' => $is_visible_by_default,
                                'is_visible_when_logged_in' => false,
                                'created_at' => $created_at,
                                'created_by' => $partner->id,
                            ]);

                            // Add background image if available
                            if ($imageCount > 0) {
                                // In seed-all-images mode, use sequential images (1.jpg, 2.jpg, etc.)
                                // Otherwise, pick random unique images
                                if ($seedAllImages) {
                                    $imageNumber = $cardCount + 1; // Sequential: 1, 2, 3...
                                } else {
                                    $imageNumber = $this->getUniqueRandomNumber($usedImageNumbers, $imageCount);
                                }
                                $background = database_path('data/demo-images/'.$businessType.'/cards/'.$imageNumber.'.jpg');

                                if (File::exists($background)) {
                                    $card
                                        ->addMedia($background)
                                        ->preservingOriginal()
                                        ->sanitizingFileName(function ($fileName) {
                                            return strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                                        })
                                        ->toMediaCollection('background', 'files');
                                }
                            }

                            // Rewards — safely pick up to $rewardsPerCard
                            if ($businessTypeIndex == 0) {
                                $rewardSlice = $partner->rewards->slice(0, $rewardsPerType);
                            } else {
                                $start = $businessTypeIndex * $rewardsPerType;
                                $rewardSlice = $partner->rewards->slice($start, $rewardsPerType);
                            }
                            $rewardCount = min($rewardsPerCard, $rewardSlice->count());
                            if ($rewardCount > 0) {
                                $rewards = $rewardSlice->random($rewardCount);
                                $card->rewards()->attach($rewards);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Get a truly random unique number using cryptographic randomness.
     */
    public function getUniqueRandomNumber(array &$usedNumbers, int $imageCount): int
    {
        // If all numbers have been used, reset the pool
        if (count($usedNumbers) >= $imageCount) {
            $usedNumbers = [];
        }

        // Pick a truly random number that hasn't been used
        do {
            $number = random_int(1, $imageCount);
        } while (in_array($number, $usedNumbers, true));

        $usedNumbers[] = $number;

        return $number;
    }
}
