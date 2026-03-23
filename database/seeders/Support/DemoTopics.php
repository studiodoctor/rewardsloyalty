<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Purpose:
 * Single source of truth for demo topics (verticals).
 */

namespace Database\Seeders\Support;

final class DemoTopics
{
    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return [
            'restaurants',
            'cafes',
            'bakery',
            'grocery',
            'beauty',
            'fitness',
            'cinema',
            'fashion',
            'electronics',
            'travel',
            'automotive',
            'pets',
        ];
    }
}
