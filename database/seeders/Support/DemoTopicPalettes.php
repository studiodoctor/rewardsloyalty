<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * Purpose:
 * Centralized, brand-forward color palettes per demo topic.
 * Used by seeders to keep the demo feeling like a coherent campaign.
 */

namespace Database\Seeders\Support;

final class DemoTopicPalettes
{
    /**
     * @return array<int, string>
     */
    public static function bgColors(string $topic): array
    {
        return match ($topic) {
            // Food
            'restaurants' => ['#EF4444', '#F97316', '#DC2626', '#F59E0B'],
            'cafes' => ['#7C2D12', '#92400E', '#B45309', '#A16207'],
            'bakery' => ['#DB2777', '#F43F5E', '#F472B6', '#C026D3'],
            'grocery' => ['#16A34A', '#22C55E', '#10B981', '#0EA5E9'],

            // Lifestyle
            'beauty' => ['#EC4899', '#A855F7', '#7C3AED', '#F43F5E'],
            'fitness' => ['#06B6D4', '#0EA5E9', '#22C55E', '#84CC16'],

            // Entertainment
            'cinema' => ['#7C3AED', '#4F46E5', '#EF4444', '#111827'],

            // Retail
            'fashion' => ['#111827', '#BE185D', '#F59E0B', '#1D4ED8'],
            'electronics' => ['#2563EB', '#4F46E5', '#0EA5E9', '#14B8A6'],

            // Services
            'travel' => ['#0EA5E9', '#06B6D4', '#3B82F6', '#10B981'],
            'automotive' => ['#111827', '#1F2937', '#EF4444', '#0EA5E9'],
            'pets' => ['#14B8A6', '#22C55E', '#F97316', '#A855F7'],

            default => ['#2563EB', '#22C55E', '#7C3AED', '#EF4444'],
        };
    }

    public static function pickBg(string $topic, int $index): string
    {
        $colors = self::bgColors($topic);

        return $colors[$index % count($colors)];
    }

    public static function pickBgSeeded(string $topic, string $seed): string
    {
        $colors = self::bgColors($topic);
        $index = (int) (abs(crc32($topic.'|'.$seed)) % count($colors));

        return $colors[$index];
    }
}
