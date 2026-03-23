<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Purpose:
 * Seeds demo vouchers with realistic multilanguage data.
 * Uses JSON files for internationalization, same pattern as StampCardSeeder.
 */

namespace Database\Seeders;

use App\Models\Card;
use App\Models\Partner;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check for seed-all-images mode (creates one voucher per image for testing backgrounds)
        $seedAllImages = env('APP_DEMO_SEED_ALL_IMAGES', false);

        $vouchersPerType = 1; // Number of vouchers per business type

        // Vouchers work best for higher-ticket items and promotional campaigns
        $businessTypes = ['fashion', 'electronics', 'travel'];

        // Voucher type configurations by business - Rich, Luxurious Palette
        // Note: free_product_name comes from JSON files (multilingual), not hardcoded here
        $voucherConfigs = [
            'fashion' => [
                ['type' => 'percentage', 'value' => 2000, 'min_purchase' => 7500, 'bg_color' => '#111827'], // Near Black, 20%
                ['type' => 'fixed_amount', 'value' => 2000, 'min_purchase' => 8000, 'bg_color' => '#BE185D'], // Deep Magenta
                ['type' => 'free_product', 'value' => 0, 'bg_color' => '#F59E0B'], // Amber
                ['type' => 'bonus_points', 'value' => 0, 'points' => 150, 'bg_color' => '#3B82F6'], // Platform Accent Blue
                ['type' => 'percentage', 'value' => 3000, 'max_discount' => 4000, 'bg_color' => '#1D4ED8'], // Royal Blue, 30%
                ['type' => 'free_product', 'value' => 0, 'bg_color' => '#C49A00'], // Rich Gold/Ochre
            ],
            'electronics' => [
                ['type' => 'percentage', 'value' => 1000, 'min_purchase' => 10000, 'bg_color' => '#2563EB'], // Bright Blue, 10%
                ['type' => 'fixed_amount', 'value' => 2500, 'min_purchase' => 20000, 'bg_color' => '#4F46E5'], // Indigo
                ['type' => 'free_product', 'value' => 0, 'bg_color' => '#0EA5E9'], // Sky Blue
                ['type' => 'bonus_points', 'value' => 0, 'points' => 300, 'bg_color' => '#3B82F6'], // Platform Accent Blue
                ['type' => 'percentage', 'value' => 1500, 'max_discount' => 5000, 'bg_color' => '#14B8A6'], // Teal, 15%
                ['type' => 'fixed_amount', 'value' => 5000, 'min_purchase' => 50000, 'bg_color' => '#202E44'], // Midnight Blue/Charcoal
            ],
            'travel' => [
                ['type' => 'percentage', 'value' => 1500, 'min_purchase' => 25000, 'bg_color' => '#0EA5E9'], // Sky Blue, 15%
                ['type' => 'fixed_amount', 'value' => 5000, 'min_purchase' => 30000, 'bg_color' => '#06B6D4'], // Cyan
                ['type' => 'free_product', 'value' => 0, 'bg_color' => '#10B981'], // Emerald
                ['type' => 'bonus_points', 'value' => 0, 'points' => 500, 'bg_color' => '#3B82F6'], // Platform Accent Blue
                ['type' => 'percentage', 'value' => 2000, 'max_discount' => 10000, 'bg_color' => '#3B82F6'], // Blue, 20%
                ['type' => 'fixed_amount', 'value' => 10000, 'min_purchase' => 50000, 'bg_color' => '#202E44'], // Midnight Blue/Charcoal
            ],
        ];

        // Max visible vouchers (one per business type for homepage variety)
        $maxVisibleByDefault = 3;

        // Get all partners with their clubs
        $partners = Partner::with('clubs')->get();

        // Loop by business type first (like CardSeeder/StampCardSeeder)
        foreach ($businessTypes as $businessTypeIndex => $businessType) {
            // Load voucher content from JSON files
            $directory = database_path('data/demo/'.$businessType.'/vouchers/');
            if (! File::exists($directory)) {
                continue; // Skip if no voucher JSON files exist for this business type
            }

            $files = File::files($directory);
            $locales = array_map(function ($file) {
                return pathinfo($file->getFilename(), PATHINFO_FILENAME);
            }, $files);

            if (empty($locales)) {
                continue;
            }

            // Count demo images for this business type
            $imageDirectory = database_path('data/demo-images/'.$businessType.'/cards/');
            $imageCount = 0;
            if (File::exists($imageDirectory)) {
                $imageFiles = File::files($imageDirectory);
                foreach ($imageFiles as $file) {
                    if ($file->getExtension() == 'jpg') {
                        $imageCount++;
                    }
                }
            }

            // Load voucher content from JSON for all locales (once per business type)
            $vouchers = [];
            foreach ($locales as $locale) {
                $jsonFilePath = database_path('data/demo/'.$businessType.'/vouchers/'.$locale.'.json');
                if (! File::exists($jsonFilePath)) {
                    continue 2; // Skip this business type if JSON file missing
                }
                $jsonString = file_get_contents($jsonFilePath);
                $vouchers[$locale] = json_decode($jsonString, true);
            }

            if (empty($vouchers)) {
                continue;
            }

            // Track used content keys and images for unique selection per business type
            $usedKeys = [];
            $usedImageNumbers = [];

            // In seed-all-images mode, create one voucher per available image
            if ($seedAllImages && $imageCount > 0) {
                $vouchersPerType = $imageCount;
            } else {
                $vouchersPerType = 1;
            }

            // Create vouchers for each business type
            for ($voucherCount = 0; $voucherCount < $vouchersPerType; $voucherCount++) {
                foreach ($partners as $partnerIndex => $partner) {
                    foreach ($partner->clubs as $clubIndex => $club) {
                        // Skip archived clubs
                        if ($club->name === 'Archived') {
                            continue;
                        }

                        // Randomly select a unique content key
                        $availableVouchers = count($vouchers[$locales[0]]);
                        if (count($usedKeys) >= $availableVouchers) {
                            // All content used, allow reuse with shuffle
                            $usedKeys = [];
                        }

                        do {
                            $key = array_rand($vouchers[$locales[0]]);
                        } while (in_array($key, $usedKeys) && count($usedKeys) < $availableVouchers);

                        $usedKeys[] = $key;

                        // Get voucher configuration for this business type
                        $configIndex = $voucherCount % count($voucherConfigs[$businessType]);
                        $config = $voucherConfigs[$businessType][$configIndex];

                        // Build translatable fields
                        $title = [];
                        $description = [];
                        $freeProductName = [];
                        $internalName = null;

                        foreach ($locales as $locale) {
                            // Use full locale keys (en_US, ar_SA, pt_BR) to match other seeders
                            $title[$locale] = $vouchers[$locale][$key]['title'];
                            $description[$locale] = $vouchers[$locale][$key]['description'];

                            // Free product name (if available in JSON) - properly multilingual
                            if (isset($vouchers[$locale][$key]['free_product_name'])) {
                                $freeProductName[$locale] = $vouchers[$locale][$key]['free_product_name'];
                            }

                            // Internal name from 'head' (English only)
                            if ($locale === 'en_US') {
                                $internalName = $vouchers[$locale][$key]['head'];
                            }
                        }

                        // Make first voucher of each business type visible (for homepage variety)
                        // Only first 3 types get visibility, and only the first voucher per type
                        $is_visible_by_default = false;

                        // In seed-all-images mode, all vouchers are visible for testing
                        if ($seedAllImages) {
                            $is_visible_by_default = true;
                        } elseif ($voucherCount == 0 && $businessTypeIndex < $maxVisibleByDefault) {
                            $is_visible_by_default = true;
                        }

                        // Generate unique voucher code
                        $codePrefix = strtoupper(substr($businessType, 0, 3));
                        $code = $codePrefix.strtoupper(substr(md5($internalName.time().rand()), 0, 6));

                        // Get first loyalty card for bonus points vouchers
                        $loyaltyCard = Card::where('club_id', $club->id)->first();

                        // Build voucher data
                        $voucherData = [
                            'club_id' => $club->id,
                            'code' => $code,
                            'name' => $internalName, // Internal name (not translatable)
                            'title' => $title, // Translatable
                            'description' => $description, // Translatable
                            'type' => $config['type'],
                            'value' => $config['value'],
                            'currency' => 'USD',
                            'is_active' => true,
                            'is_public' => true,
                            'is_visible_by_default' => $is_visible_by_default,
                            'is_single_use' => $config['type'] === 'free_product' ? true : false,
                            'is_auto_apply' => false,
                            'stackable' => false,
                            'source' => 'manual',
                            'bg_color' => $config['bg_color'],
                            'bg_color_opacity' => rand(79, 88),
                            'text_color' => '#FFFFFF',
                            'created_by' => $partner->id,
                            // In seed-all-images mode, use fixed valid dates to ensure all vouchers are active
                            'created_at' => $seedAllImages ? Carbon::now()->subMonths(2) : Carbon::now()->subDays(rand(0, 90)),
                            'valid_from' => $seedAllImages ? Carbon::now()->subMonth() : Carbon::now()->subDays(rand(1, 30)),
                            'valid_until' => $seedAllImages ? Carbon::now()->addYears(2) : Carbon::now()->addMonths(rand(6, 24)),
                        ];

                        // Type-specific fields
                        if (isset($config['min_purchase'])) {
                            $voucherData['min_purchase_amount'] = $config['min_purchase'];
                        }
                        if (isset($config['max_discount'])) {
                            $voucherData['max_discount_amount'] = $config['max_discount'];
                        }
                        // CRITICAL: Only set free_product_name if it exists in JSON (properly multilingual)
                        // If JSON doesn't have it, model will use default "Free Product"
                        if ($config['type'] === 'free_product' && ! empty($freeProductName)) {
                            $voucherData['free_product_name'] = $freeProductName;
                        }
                        if (isset($config['points'])) {
                            $voucherData['points_value'] = $config['points'];
                            if ($loyaltyCard) {
                                $voucherData['reward_card_id'] = $loyaltyCard->id;
                            }
                        }

                        // Usage limits
                        $voucherData['max_uses_total'] = rand(100, 1000);
                        $voucherData['max_uses_per_member'] = $config['type'] === 'percentage' ? 3 : 1;

                        // Random usage stats for realism (60% of vouchers have usage)
                        if (rand(0, 100) < 60) {
                            $voucherData['times_used'] = rand(1, 50);
                            $voucherData['unique_members_used'] = rand(1, $voucherData['times_used']);
                            $voucherData['total_discount_given'] = $voucherData['times_used'] * rand(500, 3000);
                        }

                        // Create voucher
                        $voucher = Voucher::create($voucherData);

                        // Add background image (truly random unique selection)
                        if ($imageCount > 0) {
                            // In seed-all-images mode, use sequential images (1.jpg, 2.jpg, etc.)
                            // Otherwise, pick random unique images
                            if ($seedAllImages) {
                                $imageNumber = $voucherCount + 1; // Sequential: 1, 2, 3...
                            } else {
                                $imageNumber = $this->getUniqueRandomNumber($usedImageNumbers, $imageCount);
                            }
                            $backgroundPath = $imageDirectory.$imageNumber.'.jpg';

                            if (File::exists($backgroundPath)) {
                                $voucher
                                    ->addMedia($backgroundPath)
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
