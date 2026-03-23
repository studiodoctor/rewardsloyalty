<?php

namespace Database\Seeders;

use App\Models\Partner;
use App\Models\Reward;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class RewardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rewardsPerType = 6; // Must match CardSeeder's $rewardsPerType for correct slicing
        // Must match CardSeeder's $businessTypes for correct reward-to-card slicing
        $businessTypes = ['restaurants', 'cafes', 'beauty', 'grocery'];

        foreach ($businessTypes as $businessType) {
            // Count demo images (skip if directory doesn't exist - e.g., on Forge deployments)
            $directory = database_path('data/demo-images/'.$businessType.'/rewards/');
            if (! File::exists($directory)) {
                continue;
            }
            $files = File::files($directory);
            $imageCount = 0;

            foreach ($files as $file) {
                if (File::extension($file) == 'jpg') {
                    $imageCount++;
                }
            }

            // Locales
            $directory = database_path('data/demo/'.$businessType.'/rewards/');
            $files = File::files($directory);

            $locales = array_map(function ($file) {
                return pathinfo($file, PATHINFO_FILENAME);
            }, $files);

            // Rewards
            foreach ($locales as $locale) {
                $jsonFilePath = database_path('data/demo/'.$businessType.'/rewards/'.$locale.'.json');
                $jsonString = file_get_contents($jsonFilePath);
                $rewards[$locale] = json_decode($jsonString, true);
            }

            // Get the partners from the database
            $partners = Partner::all();

            // Track used keys and images across ALL partners for this business type
            $usedKeys = [];
            $usedImageNumbers = [];

            // Limit to available rewards to prevent infinite loop
            $availableRewards = count($rewards[$locales[0]]);
            $rewardsToCreate = min($rewardsPerType, $availableRewards);

            foreach ($partners as $partner) {
                for ($i = 0; $i < $rewardsToCreate; $i++) {
                    $created_at = fake()->dateTimeBetween('-32 week', '-6 week');
                    $active_from = fake()->dateTimeBetween('-32 week', '-1 day');
                    $expiration_date = fake()->dateTimeBetween('+2 week', '+64 week');

                    // Points
                    $values = [10, 10, 25, 25, 100, 100, 100, 100, 100, 150, 200, 250, 300, 400, 500, 500, 750, 1000, 1500, 2000, 2500, 3000, 4000, 5000, 7500, 10000];
                    $points = $values[array_rand($values)];

                    // Generate a unique random key (track globally per business type)
                    if (count($usedKeys) >= $availableRewards) {
                        // All content used, reset to allow reuse
                        $usedKeys = [];
                    }

                    do {
                        $randomKey = array_rand($rewards[$locales[0]]);
                    } while (in_array($randomKey, $usedKeys) && count($usedKeys) < $availableRewards);

                    $usedKeys[] = $randomKey;

                    foreach ($locales as $locale) {
                        // $faker = Factory::create($locale);
                        // $title[$locale] = $faker->sentence(3);
                        // $description[$locale] = $faker->realText(128);
                        $title[$locale] = $rewards[$locale][$randomKey]['title'];
                        $description[$locale] = $rewards[$locale][$randomKey]['description'];
                    }

                    $reward = Reward::create([
                        'name' => $rewards['en_US'][$randomKey]['title'], // fake()->sentence(rand(1,2)),
                        'title' => $title,
                        'description' => $description,
                        'max_number_to_redeem' => 0,
                        'points' => $points,
                        'active_from' => $active_from,
                        'expiration_date' => $expiration_date,
                        'is_active' => true,
                        'is_undeletable' => env('APP_IS_UNEDITABLE', true),
                        'is_uneditable' => env('APP_IS_UNEDITABLE', true),
                        'number_of_times_redeemed' => 0,
                        'views' => 0,
                        'created_at' => $created_at,
                        'created_by' => $partner->id,
                    ]);

                    // Add images if available (truly random unique selection)
                    if ($imageCount > 0) {
                        for ($imageSlot = 1; $imageSlot <= 3; $imageSlot++) {
                            $imageNumber = $this->getUniqueRandomNumber($usedImageNumbers, $imageCount);
                            $image = database_path('data/demo-images/'.$businessType.'/rewards/'.$imageNumber.'.jpg');

                            if (File::exists($image)) {
                                $reward
                                    ->addMedia($image)
                                    ->preservingOriginal()
                                    ->sanitizingFileName(function ($fileName) {
                                        return strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                                    })
                                    ->toMediaCollection('image'.$imageSlot, 'files');
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
