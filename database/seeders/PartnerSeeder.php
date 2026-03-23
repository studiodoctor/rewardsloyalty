<?php

namespace Database\Seeders;

use App\Models\Partner;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Partner::create([
            'name' => 'Partner',
            'email' => 'partner@example.com',
            'password' => bcrypt(env('APP_DEMO_PASSWORD', 'welcome3210')),
            'role' => 1,
            'email_verified_at' => Carbon::now('UTC'),
            'is_active' => true,
            'is_undeletable' => env('APP_IS_UNEDITABLE', true),
            'is_uneditable' => env('APP_IS_UNEDITABLE', true),
            'created_at' => Carbon::now('UTC'),
            'locale' => config('app.locale'),
            'currency' => config('default.currency'),
            'time_zone' => config('default.time_zone'),
            'meta' => [
                'cards_on_homepage' => true,
                'loyalty_cards_permission' => true,
                'loyalty_cards_limit' => -1,
                'rewards_limit' => -1,
                'stamp_cards_permission' => true,
                'stamp_cards_limit' => -1,
                'vouchers_permission' => true,
                'voucher_batches_permission' => true,
                'vouchers_limit' => -1,
                'staff_members_limit' => -1,
                'email_campaigns_permission' => true,
                'activity_permission' => true,
                'agent_api_permission' => true,
                'agent_keys_limit' => 5,
            ],
        ]);

        // No additional demo partners for now, is confusing
        for ($i = 0; $i < 0; $i++) {
            $gender = (fake()->boolean) ? 'male' : 'female';
            $created_at = fake()->dateTimeBetween('-78 week', '-6 week');
            $partner = Partner::create([
                'name' => fake()->name($gender),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt(env('APP_DEMO_PASSWORD', 'welcome3210')),
                'role' => 1,
                'email_verified_at' => $created_at,
                'is_active' => fake()->boolean(90),
                'number_of_times_logged_in' => mt_rand(1, 44),
                'last_login_at' => fake()->dateTimeBetween('-12 week', '-1 day'),
                'is_undeletable' => env('APP_IS_UNEDITABLE', true),
                'is_uneditable' => env('APP_IS_UNEDITABLE', true),
                'created_at' => $created_at,
                'locale' => config('app.locale'),
                'currency' => config('default.currency'),
                'time_zone' => config('default.time_zone'),
            ]);

            // Randomly generate an avatar for some of the partners
            if (fake()->boolean(74)) {
                $genderDir = ($gender == 'male') ? 'men' : 'women';
                $avatar = database_path("data/demo-images/avatars/$genderDir/".fake()->numberBetween(2, 99).'.jpg');

                if (File::exists($avatar)) {
                    $partner
                        ->addMedia($avatar)
                        ->preservingOriginal()
                        ->sanitizingFileName(function ($fileName) {
                            return strtolower(str_replace(['#', '/', '\\', ' '], '-', $fileName));
                        })
                        ->toMediaCollection('avatar', 'files');
                }
            }
        }
    }
}
