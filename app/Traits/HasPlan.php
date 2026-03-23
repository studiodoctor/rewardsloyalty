<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 *
 * HasPlan Trait
 *
 * Provides plan-based functionality for models (primarily Partner).
 * Plans are config-based (config/plans.php) - no database table needed.
 *
 * Features:
 * - Plan configuration access
 * - Limit checking (max_clubs, max_members, etc.)
 * - Plan comparison (hasPlanOrHigher)
 * - Pricing utilities
 * - Upgrade options
 */

namespace App\Traits;

trait HasPlan
{
    /**
     * Get current plan config.
     */
    public function getPlanConfig(): array
    {
        $config = config("plans.{$this->plan}", []);

        if (empty($config)) {
            return static::getDefaultPlanConfig();
        }

        return array_merge(['key' => $this->plan], $config);
    }

    /**
     * Get default plan config.
     */
    public static function getDefaultPlanConfig(): array
    {
        foreach (config('plans', []) as $key => $plan) {
            if ($plan['is_default'] ?? false) {
                return array_merge(['key' => $key], $plan);
            }
        }

        return array_merge(['key' => 'bronze'], config('plans.bronze', []));
    }

    /**
     * Get default plan key.
     */
    public static function getDefaultPlan(): string
    {
        foreach (config('plans', []) as $key => $plan) {
            if ($plan['is_default'] ?? false) {
                return $key;
            }
        }

        return 'bronze';
    }

    /**
     * Get plan display name.
     */
    public function getPlanName(): string
    {
        return $this->getPlanConfig()['name'] ?? 'Bronze';
    }

    /**
     * Get plan description.
     */
    public function getPlanDescription(): string
    {
        return $this->getPlanConfig()['description'] ?? '';
    }

    /**
     * Get a limit value (-1 = unlimited).
     */
    public function getLimit(string $key): int
    {
        $key = str_starts_with($key, 'max_') ? $key : "max_{$key}";

        return $this->getPlanConfig()[$key] ?? 0;
    }

    /**
     * Check if within limit (-1 = unlimited).
     */
    public function withinLimit(string $key, int $current): bool
    {
        $max = $this->getLimit($key);

        return $max === -1 || $current < $max;
    }

    /**
     * Check if at or over limit.
     */
    public function atLimit(string $key, int $current): bool
    {
        return ! $this->withinLimit($key, $current);
    }

    /**
     * Get remaining quota (-1 = unlimited).
     */
    public function getRemainingQuota(string $key, int $current): int
    {
        $max = $this->getLimit($key);

        return $max === -1 ? -1 : max(0, $max - $current);
    }

    /**
     * Check if on free plan.
     */
    public function isFreePlan(): bool
    {
        return ($this->getPlanConfig()['price_monthly'] ?? 0) === 0;
    }

    /**
     * Check if on paid plan.
     */
    public function isPaidPlan(): bool
    {
        return ! $this->isFreePlan();
    }

    /**
     * Check if plan is at least a certain level.
     */
    public function hasPlanOrHigher(string $plan): bool
    {
        $currentOrder = $this->getPlanConfig()['sort_order'] ?? 0;
        $targetOrder = config("plans.{$plan}.sort_order", 0);

        return $currentOrder >= $targetOrder;
    }

    /**
     * Check if current plan matches given plan.
     */
    public function hasPlan(string $plan): bool
    {
        return $this->plan === $plan;
    }

    /**
     * Get all visible plans for pricing display.
     */
    public static function getVisiblePlans(): array
    {
        return collect(config('plans', []))
            ->filter(fn ($plan) => ($plan['is_active'] ?? true) && ($plan['is_visible'] ?? true))
            ->sortBy('sort_order')
            ->map(fn ($plan, $key) => array_merge(['key' => $key], $plan))
            ->values()
            ->all();
    }

    /**
     * Get all active plans.
     */
    public static function getActivePlans(): array
    {
        return collect(config('plans', []))
            ->filter(fn ($plan) => $plan['is_active'] ?? true)
            ->sortBy('sort_order')
            ->map(fn ($plan, $key) => array_merge(['key' => $key], $plan))
            ->values()
            ->all();
    }

    /**
     * Get popular plan (for highlighting).
     */
    public static function getPopularPlan(): ?array
    {
        foreach (config('plans', []) as $key => $plan) {
            if ($plan['is_popular'] ?? false) {
                return array_merge(['key' => $key], $plan);
            }
        }

        return null;
    }

    /**
     * Format price for display.
     */
    public function formatPrice(string $type = 'monthly'): string
    {
        $config = $this->getPlanConfig();
        $price = $type === 'yearly' ? ($config['price_yearly'] ?? 0) : ($config['price_monthly'] ?? 0);
        $currency = $config['currency'] ?? 'USD';

        if ($price === 0) {
            return trans('common.free');
        }

        return number_format($price / 100, 2).' '.$currency;
    }

    /**
     * Get monthly price in cents.
     */
    public function getMonthlyPrice(): int
    {
        return $this->getPlanConfig()['price_monthly'] ?? 0;
    }

    /**
     * Get yearly price in cents.
     */
    public function getYearlyPrice(): int
    {
        return $this->getPlanConfig()['price_yearly'] ?? 0;
    }

    /**
     * Get currency code.
     */
    public function getPlanCurrency(): string
    {
        return $this->getPlanConfig()['currency'] ?? 'USD';
    }

    /**
     * Get upgrade options (plans higher than current).
     */
    public function getUpgradeOptions(): array
    {
        $currentOrder = $this->getPlanConfig()['sort_order'] ?? 0;

        return collect(config('plans', []))
            ->filter(fn ($plan) => ($plan['is_active'] ?? true) && ($plan['sort_order'] ?? 0) > $currentOrder)
            ->sortBy('sort_order')
            ->map(fn ($plan, $key) => array_merge(['key' => $key], $plan))
            ->values()
            ->all();
    }

    /**
     * Check if upgrade is available.
     */
    public function canUpgrade(): bool
    {
        return ! empty($this->getUpgradeOptions());
    }

    /**
     * Get downgrade options (plans lower than current).
     */
    public function getDowngradeOptions(): array
    {
        $currentOrder = $this->getPlanConfig()['sort_order'] ?? 0;

        return collect(config('plans', []))
            ->filter(fn ($plan) => ($plan['is_active'] ?? true) && ($plan['sort_order'] ?? 0) < $currentOrder)
            ->sortBy('sort_order')
            ->map(fn ($plan, $key) => array_merge(['key' => $key], $plan))
            ->values()
            ->all();
    }

    /**
     * Check if downgrade is available.
     */
    public function canDowngrade(): bool
    {
        return ! empty($this->getDowngradeOptions());
    }

    /**
     * Get a specific plan config by key.
     */
    public static function getPlanByKey(string $key): ?array
    {
        $plan = config("plans.{$key}");

        if (empty($plan)) {
            return null;
        }

        return array_merge(['key' => $key], $plan);
    }

    /**
     * Check if a plan key is valid.
     */
    public static function isValidPlan(string $key): bool
    {
        return ! empty(config("plans.{$key}"));
    }
}
