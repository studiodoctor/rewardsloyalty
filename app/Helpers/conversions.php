<?php

declare(strict_types=1);

/**
 * Reward Loyalty - Proprietary Software
 * Copyright (c) 2025 NowSquare. All rights reserved.
 * See LICENSE file for terms.
 */

/**
 * Format bytes to human-readable units (KB, MB, GB, TB).
 *
 * @param  int  $size  The size in bytes to be formatted.
 * @param  int  $precision  The number of decimal places to display (default: 2).
 * @return string The formatted size with the appropriate unit.
 */
function formatBytes(int $size, int $precision = 2): string
{
    if ($size > 0) {
        $base = log($size, 1024);
        $suffixes = ['bytes', 'KB', 'MB', 'GB', 'TB'];

        return round(pow(1024, $base - floor($base)), $precision).$suffixes[floor($base)];
    } else {
        return '0 bytes';
    }
}

/**
 * Calculate the greatest common divisor (GCD) of two numbers.
 *
 * This function uses the Euclidean algorithm to find the largest number
 * that divides both of the given numbers without leaving a remainder.
 *
 * @param  int  $a  The first number.
 * @param  int  $b  The second number.
 * @return int The greatest common divisor of $a and $b.
 */
function gcd($a, $b)
{
    return ($a % $b) ? gcd($b, $a % $b) : $b;
}

/**
 * Format a monetary amount with currency symbol.
 *
 * Converts an amount (in cents/smallest currency unit) to a formatted
 * string with the appropriate currency symbol and decimal places.
 *
 * @param  int|float|null  $amount  The amount in cents (smallest currency unit).
 * @param  string|null  $currency  The ISO 4217 currency code (e.g., 'USD', 'EUR', 'GBP'). Falls back to 'USD' if null.
 * @param  string|null  $locale  The locale to use for formatting (defaults to app locale).
 * @return string The formatted monetary amount with currency symbol.
 */
function money(int|float|null $amount, ?string $currency = 'USD', ?string $locale = null): string
{
    if ($amount === null) {
        $amount = 0;
    }

    // Convert from cents to actual amount
    $actualAmount = $amount / 100;

    // Fall back to USD if currency is null
    $currency = $currency ?? 'USD';

    // Use app locale if none provided
    $locale = $locale ?? app()->getLocale();

    // Format using NumberFormatter for proper currency display
    $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);

    return $formatter->formatCurrency($actualAmount, $currency);
}

/**
 * Format a monetary amount (already in currency units) with currency symbol.
 *
 * Formats a decimal currency amount without conversion. Use this when your
 * database stores values as DECIMAL (e.g., 10.50) instead of cents (1050).
 *
 * @param  int|float|null  $amount  The amount in currency units (e.g., 10.50 for $10.50).
 * @param  string|null  $currency  The ISO 4217 currency code (e.g., 'USD', 'EUR', 'GBP'). Falls back to config default.
 * @param  string|null  $locale  The locale to use for formatting (defaults to app locale).
 * @return string The formatted monetary amount with currency symbol.
 */
function moneyFormat(int|float|null $amount, ?string $currency = null, ?string $locale = null): string
{
    if ($amount === null) {
        $amount = 0;
    }
    // Fall back to config default, then USD as safety net
    $currency = $currency ?? config('default.currency');
    // Use app locale if none provided
    $locale = $locale ?? app()->getLocale();
    // Format using NumberFormatter for proper currency display
    $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
    $formatted = $formatter->formatCurrency($amount, $currency);

    // Replace non-breaking space with regular space for proper text wrapping
    return str_replace("\u{00A0}", ' ', $formatted);
}
