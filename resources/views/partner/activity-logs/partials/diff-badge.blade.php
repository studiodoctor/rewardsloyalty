{{--
  Reward Loyalty - Proprietary Software
  Copyright (c) 2025 NowSquare. All rights reserved.
  See LICENSE file for terms.

  Difference Badge Partial - Shows percentage change with color coding
--}}

@php
    $numDiff = intval(str_replace('+', '', $diff));
@endphp

@if($diff === '0' || $diff === '+0')
    <span class="bg-secondary-100 text-secondary-800 text-xs font-medium inline-flex items-center px-2.5 py-1 rounded-md dark:bg-secondary-800 dark:text-secondary-300">
        {{ $diff }}%
    </span>
@elseif($numDiff > 0)
    <span class="bg-emerald-100 text-emerald-800 text-xs font-medium inline-flex items-center px-2.5 py-1 rounded-md dark:bg-emerald-900/30 dark:text-emerald-400">
        <x-ui.icon icon="trending-up" class="w-3 h-3 mr-1" />
        {{ $diff }}%
    </span>
@else
    <span class="bg-red-100 text-red-800 text-xs font-medium inline-flex items-center px-2.5 py-1 rounded-md dark:bg-red-900/30 dark:text-red-400">
        <x-ui.icon icon="trending-down" class="w-3 h-3 mr-1" />
        {{ $diff }}%
    </span>
@endif

