<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 */

namespace Database\Seeders;

use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Member::create([
            'name' => 'Member',
            'email' => 'member@example.com',
            'password' => bcrypt(env('APP_DEMO_PASSWORD', 'welcome3210')),
            'role' => 1, // 1 = user
            'email_verified_at' => Carbon::now('UTC'),
            'is_active' => true,
            'accepts_emails' => true,
            'is_undeletable' => env('APP_IS_UNEDITABLE', true),
            'is_uneditable' => env('APP_IS_UNEDITABLE', true),
            'created_at' => Carbon::now('UTC'),
            'locale' => config('app.locale'),
            'currency' => config('default.currency'),
            'time_zone' => config('default.time_zone'),
        ]);

        // Demo requirement: total 9 members (1 fixed + 8 random).
        for ($i = 0; $i < 8; $i++) {
            $created_at = fake()->dateTimeBetween('-78 week', '-6 week');
            Member::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => bcrypt(env('APP_DEMO_PASSWORD', 'welcome3210')),
                'role' => 1, // 1 = user
                'email_verified_at' => $created_at,
                'is_active' => true,
                'accepts_emails' => true,
                'is_undeletable' => env('APP_IS_UNEDITABLE', true),
                'is_uneditable' => env('APP_IS_UNEDITABLE', true),
                'created_at' => $created_at,
                'locale' => config('app.locale'),
                'currency' => config('default.currency'),
                'time_zone' => config('default.time_zone'),
            ]);
        }
    }
}
