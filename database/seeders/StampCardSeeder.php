<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Purpose:
 * Seeds demo stamp card programs with realistic data.
 * Uses same design approach as loyalty cards for consistency.
 */

namespace Database\Seeders;

use App\Models\Card;
use App\Models\Partner;
use App\Models\StampCard;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class StampCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check for seed-all-images mode (creates one stamp card per image for testing backgrounds)
        $seedAllImages = env('APP_DEMO_SEED_ALL_IMAGES', false);

        // Global counter for cycling stamp counts 1-20 across all categories
        $globalStampCounter = 0;

        $stampCardsPerType = 1;
        // Stamp cards work best for simple, predictable visit patterns ("10th coffee free")
        $businessTypes = ['cafes', 'bakery', 'beauty'];

        // Stamp icons by business type (simple, iconic shapes that render well at small sizes)
        $stampIcons = [
            'restaurants' => ['🍕', '🍔', '🍜', '🥗', '🍱', '🍴'],
            'cinema' => ['🎬', '🍿', '🎭', '🎫', '⭐', '🎥'],
            'beauty' => ['💅', '💄', '✨', '💎', '🌸', '💫'],
            'fitness' => ['💪', '⚡', '🏆', '⭐', '🎯', '❤️'],
            'bakery' => ['🥐', '🍩', '🧁', '🍪', '🎂', '🥖'],
            'cafes' => ['☕', '🫖', '🧋', '🍵', '✨', '💫'],
            'electronics' => ['📱', '💻', '🎧', '⌚', '🎮', '⚡'],
            'fashion' => ['👗', '👠', '👜', '💎', '✨', '🛍️'],
            'travel' => ['✈️', '🌴', '🧳', '🌍', '⛱️', '⭐'],
            'grocery' => ['🛒', '🍎', '🥕', '🥬', '🍞', '🧺'],
        ];

        // Max visible stamp cards (one per business type for homepage variety)
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
            $directory = database_path('data/demo/'.$businessType.'/stamp-cards/');
            if (! File::exists($directory)) {
                $directory = database_path('data/demo/'.$businessType.'/cards/');
            }
            if (! File::exists($directory)) {
                continue;
            }
            $files = File::files($directory);

            $locales = array_map(function ($file) {
                return pathinfo($file->getFilename(), PATHINFO_FILENAME);
            }, $files);

            // Track used keys and images across ALL stamp cards for this business type
            $usedKeys = [];
            $usedImageNumbers = [];

            // In seed-all-images mode, create one stamp card per available image
            if ($seedAllImages && $imageCount > 0) {
                $stampCardsPerType = $imageCount;
            } else {
                $stampCardsPerType = 1;
            }

            // Load stamp card content once for all iterations
            foreach ($locales as $locale) {
                $jsonFilePath = database_path('data/demo/'.$businessType.'/stamp-cards/'.$locale.'.json');
                if (! File::exists($jsonFilePath)) {
                    $jsonFilePath = database_path('data/demo/'.$businessType.'/cards/'.$locale.'.json');
                }
                $jsonString = file_get_contents($jsonFilePath);
                $stampCards[$locale] = json_decode($jsonString, true);
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

            for ($stampCardCount = 0; $stampCardCount < $stampCardsPerType; $stampCardCount++) {
                $colorCounter = ($businessTypeIndex == 0) ? 0 : $businessTypeIndex + 2;
                $colorCounter += $stampCardCount;

                foreach ($partners as $index => $partner) {

                    foreach ($partner->clubs as $club) {
                        for ($i = 0; $i < 1; $i++) {
                            // In seed-all-images mode, use fixed valid dates to ensure all cards are active
                            if ($seedAllImages) {
                                $created_at = Carbon::now()->subMonths(2)->setTimezone('UTC');
                                $valid_from = Carbon::now()->subMonth()->setTimezone('UTC');
                                $valid_until = Carbon::now()->addYears(5)->setTimezone('UTC');
                            } else {
                                // Use Carbon and UTC to avoid DST issues
                                $created_at = Carbon::parse(fake()->dateTimeBetween('-14 month', '-4 month'))->setTimezone('UTC');
                                $valid_from = Carbon::parse(fake()->dateTimeBetween('-4 month', '-3 month'))->setTimezone('UTC');
                                $valid_until = Carbon::parse(fake()->dateTimeBetween('+1 year', '+7 year'))->setTimezone('UTC');
                            }

                            // Color scheme with matching stamp colors
                            $bg_color = $colorValues[$colorCounter % count($colorValues)];

                            // Generate coordinated stamp colors based on background
                            $stampColorSchemes = [
                                '#0047AB' => ['stamp' => '#93C5FD', 'empty' => '#DBEAFE'], // Deep Sapphire Blue -> Light blue shades
                                '#B50031' => ['stamp' => '#FCA5A5', 'empty' => '#FEE2E2'], // Ferrari Red -> Light red shades
                                '#007A65' => ['stamp' => '#5EEAD4', 'empty' => '#CCFBF1'], // Muted Deep Teal -> Light teal shades
                                '#C49A00' => ['stamp' => '#FCD34D', 'empty' => '#FEF3C7'], // Rich Gold/Ochre -> Light amber shades
                                '#7045AF' => ['stamp' => '#C4B5FD', 'empty' => '#EDE9FE'], // Deep Plum/Violet -> Light violet shades
                                '#202E44' => ['stamp' => '#94A3B8', 'empty' => '#E2E8F0'], // Midnight Blue/Charcoal -> Light slate shades
                                '#F26419' => ['stamp' => '#FDBA74', 'empty' => '#FFEDD5'], // Rich Burnt Orange -> Light orange shades
                                '#3B82F6' => ['stamp' => '#93C5FD', 'empty' => '#DBEAFE'], // Platform Accent Blue -> Light blue shades
                                '#10B981' => ['stamp' => '#6EE7B7', 'empty' => '#D1FAE5'], // Vivid Emerald -> Light emerald shades
                                '#DA291C' => ['stamp' => '#FCA5A5', 'empty' => '#FEE2E2'], // True Scarlet -> Light red shades
                                '#A37A00' => ['stamp' => '#FCD34D', 'empty' => '#FEF3C7'], // Deep Bronze -> Light amber shades
                                '#E55D87' => ['stamp' => '#F9A8D4', 'empty' => '#FCE7F3'], // Dusty Rose -> Light pink shades
                            ];

                            $stamp_color = $stampColorSchemes[$bg_color]['stamp'] ?? '#10B981';
                            $empty_stamp_color = $stampColorSchemes[$bg_color]['empty'] ?? '#E5E7EB';

                            $colorCounter++;

                            // Stamp configuration
                            // In seed-all-images mode, cycle through specific stamp counts across all categories
                            if ($seedAllImages) {
                                $stampCountOptions = [5, 6, 8, 9, 10, 12];
                                $stamps_required = $stampCountOptions[$globalStampCounter % count($stampCountOptions)];
                                $globalStampCounter++;
                            } else {
                                $stamps_required_options = [6, 8, 9, 10, 12];
                                $stamps_required = $stamps_required_options[array_rand($stamps_required_options)];
                            }

                            // Minimum purchase (50% chance of having one) - stored in currency units (dollars/euros)
                            // Values: $2.00, $3.00, $5.00, $7.50, $10.00
                            $min_purchase = (rand(0, 100) < 50) ? [2.00, 3.00, 5.00, 7.50, 10.00][array_rand([2.00, 3.00, 5.00, 7.50, 10.00])] : null;

                            // Daily limit (30% chance of having one)
                            $max_stamps_per_day = (rand(0, 100) < 30) ? [1, 2, 3][array_rand([1, 2, 3])] : null;

                            // Select stamp icon
                            $stamp_icon = $stampIcons[$businessType][array_rand($stampIcons[$businessType])];

                            // Reward points (70% get points reward)
                            $reward_points = null;
                            $reward_card_id = null;
                            if (rand(0, 100) < 70) {
                                $loyaltyCard = Card::where('club_id', $club->id)->inRandomOrder()->first();
                                if ($loyaltyCard) {
                                    $reward_card_id = $loyaltyCard->id;
                                    $reward_points_options = [100, 250, 500, 1000, 1500];
                                    $reward_points = $reward_points_options[array_rand($reward_points_options)];
                                }
                            }

                            // Physical claim (50% chance if no points reward)
                            $requires_physical_claim = ($reward_points === null && rand(0, 100) < 50);

                            // Pick a unique stamp card key, or reuse if exhausted
                            $availableStampCards = count($stampCards[$locales[0]]);
                            if (count($usedKeys) >= $availableStampCards) {
                                // All stamp cards used, allow reuse
                                $randomKey = array_rand($stampCards[$locales[0]]);
                            } else {
                                do {
                                    $randomKey = array_rand($stampCards[$locales[0]]);
                                } while (isset($usedKeys[$randomKey]));
                            }
                            $usedKeys[$randomKey] = true;

                            // Build translatable fields
                            foreach ($locales as $locale) {
                                $name[$locale] = $stampCards[$locale][$randomKey]['head'].' '.trans('common.stamp_card', [], $locale);
                                $title[$locale] = $stampCards[$locale][$randomKey]['title'];
                                $description[$locale] = $stampCards[$locale][$randomKey]['description'];

                                if ($requires_physical_claim) {
                                    $reward_title[$locale] = trans('common.free', [], $locale).' '.$stampCards[$locale][$randomKey]['head'];
                                    $reward_description[$locale] = trans('common.get_free_item_after_completion', [], $locale);
                                } elseif ($reward_points) {
                                    $reward_title[$locale] = number_format($reward_points).' '.trans('common.points', [], $locale);
                                    $reward_description[$locale] = trans('common.bonus_points_reward', [], $locale);
                                } else {
                                    $reward_title[$locale] = trans('common.special_reward', [], $locale);
                                    $reward_description[$locale] = trans('common.ask_staff_for_details', [], $locale);
                                }
                            }

                            $is_active = true;
                            $is_visible_by_default = false;

                            // In seed-all-images mode, all stamp cards are visible for testing
                            if ($seedAllImages) {
                                $is_visible_by_default = true;
                            }
                            // Make first stamp card of each business type visible (for homepage variety)
                            // Only first 3 types get visibility, and only the first card per type
                            elseif ($club->name != 'Archived' && $stampCardCount == 0 && $businessTypeIndex < $maxVisibleByDefault) {
                                $is_visible_by_default = true;
                            }

                            $stampCard = StampCard::create([
                                'club_id' => $club->id,
                                'name' => $stampCards['en_US'][$randomKey]['head'].' Stamp Card',
                                'title' => $title,
                                'description' => $description,
                                'stamp_icon' => $stamp_icon,
                                'stamps_required' => $stamps_required,
                                'min_purchase_amount' => $min_purchase,
                                'max_stamps_per_transaction' => 1,
                                'max_stamps_per_day' => $max_stamps_per_day,
                                'reward_points' => $reward_points,
                                'reward_card_id' => $reward_card_id,
                                'reward_title' => $reward_title,
                                'reward_description' => $reward_description,
                                'requires_physical_claim' => $requires_physical_claim,
                                'valid_from' => $valid_from,
                                'valid_until' => $valid_until,
                                'is_active' => $is_active,
                                'is_visible_by_default' => $is_visible_by_default,
                                'is_undeletable' => env('APP_IS_UNEDITABLE', true),
                                'bg_color' => $bg_color,
                                'bg_color_opacity' => rand(79, 88),
                                'text_color' => '#ffffff',
                                'stamp_color' => $stamp_color,
                                'empty_stamp_color' => $empty_stamp_color,
                                'currency' => 'USD',
                                'created_at' => $created_at,
                                'created_by' => $partner->id,
                            ]);

                            // Add background image if available
                            if ($imageCount > 0) {
                                // In seed-all-images mode, use sequential images (1.jpg, 2.jpg, etc.)
                                // Otherwise, pick random unique images
                                if ($seedAllImages) {
                                    $imageNumber = $stampCardCount + 1; // Sequential: 1, 2, 3...
                                } else {
                                    $imageNumber = $this->getUniqueRandomNumber($usedImageNumbers, $imageCount);
                                }
                                $background = database_path('data/demo-images/'.$businessType.'/cards/'.$imageNumber.'.jpg');

                                if (File::exists($background)) {
                                    $stampCard
                                        ->addMedia($background)
                                        ->preservingOriginal()
                                        ->sanitizingFileName(function ($fileName) {
                                            return strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                                        })
                                        ->toMediaCollection('background', 'files');
                                }
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
    private function getUniqueRandomNumber(array &$usedNumbers, int $imageCount): int
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
