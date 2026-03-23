<?php

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Partner Subscription Plans Configuration
 *
 * This file defines all available subscription plans for partners.
 * Plans are config-based (no database table needed) for simplicity.
 *
 * Pricing Notes:
 * - Prices are stored in CENTS (integers) to avoid floating point issues
 * - Limit of -1 means UNLIMITED
 * - is_default determines which plan new partners receive
 * - is_popular highlights a plan on pricing pages
 * - sort_order determines plan hierarchy for hasPlanOrHigher() checks
 */

return [

    // ─────────────────────────────────────────────────────────────────────────
    // BRONZE (FREE TIER)
    // ─────────────────────────────────────────────────────────────────────────

    'bronze' => [
        'name' => 'Bronze',
        'description' => 'Get started with the basics',
        'price_monthly' => 0,
        'price_yearly' => 0,
        'currency' => 'USD',
        'is_active' => true,
        'is_default' => true,
        'is_visible' => true,
        'is_popular' => false,
        'sort_order' => 0,
        'max_clubs' => 1,
        'max_members' => 100,
        'max_staff' => 1,
        'max_locations' => 1,
        'max_cards' => 1,
        'max_rewards' => 3,
        'max_promoted_cards' => 0,
    ],

    // ─────────────────────────────────────────────────────────────────────────
    // SILVER (STARTER PAID TIER)
    // ─────────────────────────────────────────────────────────────────────────

    'silver' => [
        'name' => 'Silver',
        'description' => 'Perfect for small businesses',
        'price_monthly' => 2900,    // $29.00/month
        'price_yearly' => 29000,    // $290.00/year (2 months free)
        'currency' => 'USD',
        'is_active' => true,
        'is_default' => false,
        'is_visible' => true,
        'is_popular' => false,
        'sort_order' => 1,
        'max_clubs' => 1,
        'max_members' => 1000,
        'max_staff' => 5,
        'max_locations' => 1,
        'max_cards' => 3,
        'max_rewards' => 15,
        'max_promoted_cards' => 1,
    ],

    // ─────────────────────────────────────────────────────────────────────────
    // GOLD (GROWTH TIER)
    // ─────────────────────────────────────────────────────────────────────────

    'gold' => [
        'name' => 'Gold',
        'description' => 'For growing businesses with multiple locations',
        'price_monthly' => 7900,    // $79.00/month
        'price_yearly' => 79000,    // $790.00/year (2 months free)
        'currency' => 'USD',
        'is_active' => true,
        'is_default' => false,
        'is_visible' => true,
        'is_popular' => true,       // Highlighted on pricing page
        'sort_order' => 2,
        'max_clubs' => 3,
        'max_members' => 10000,
        'max_staff' => 25,
        'max_locations' => 10,
        'max_cards' => 10,
        'max_rewards' => 50,
        'max_promoted_cards' => 5,
    ],

    // ─────────────────────────────────────────────────────────────────────────
    // PLATINUM (ENTERPRISE TIER)
    // ─────────────────────────────────────────────────────────────────────────

    'platinum' => [
        'name' => 'Platinum',
        'description' => 'Unlimited power for agencies and enterprises',
        'price_monthly' => 19900,   // $199.00/month
        'price_yearly' => 199000,   // $1,990.00/year (2 months free)
        'currency' => 'USD',
        'is_active' => true,
        'is_default' => false,
        'is_visible' => true,
        'is_popular' => false,
        'sort_order' => 3,
        'max_clubs' => -1,          // Unlimited
        'max_members' => -1,        // Unlimited
        'max_staff' => -1,          // Unlimited
        'max_locations' => -1,      // Unlimited
        'max_cards' => -1,          // Unlimited
        'max_rewards' => -1,        // Unlimited
        'max_promoted_cards' => -1, // Unlimited
    ],

];
