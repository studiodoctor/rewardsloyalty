{{--
  Reward Loyalty - Proprietary Software
  Copyright (c) 2025 NowSquare. All rights reserved.
  See LICENSE file for terms.

  Activity Log Analytics Dashboard - Admin View
  Provides comprehensive system-wide audit trail insights.
--}}

@extends('admin.layouts.default')

@section('page_title', trans('common.activity_log_analytics') . config('default.page_title_delimiter') . config('default.app_name'))

@section('content')
<div class="w-full max-w-7xl mx-auto px-4 md:px-6 py-6 md:py-8">
    
    {{-- Page Header with Actions --}}
        <div>
        <x-ui.page-header
            icon="activity"
            :title="trans('common.activity_log_analytics')"
            :description="trans('common.system_wide_audit_insights')"
        >
            <x-slot name="actions">
                <div class="flex items-center gap-4">
                        {{-- Range Selector --}}
                        <div class="relative group min-w-[200px]">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
                                <x-ui.icon icon="calendar" class="h-5 w-5 text-secondary-400 group-hover:text-primary-500 transition-colors" />
                            </div>
                            <select id="range"
                                class="appearance-none w-full bg-white dark:bg-secondary-900 border border-secondary-200 dark:border-secondary-800 text-secondary-700 dark:text-secondary-300 text-sm rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 pl-12 pr-12 py-3 transition-all cursor-pointer hover:border-secondary-300 dark:hover:border-secondary-700 shadow-sm hover:shadow-md">
                                <option value="7" @if($range == '7') selected @endif>{{ trans('common.last_7_days') }}</option>
                                <option value="14" @if($range == '14') selected @endif>{{ trans('common.last_14_days') }}</option>
                                <option value="30" @if($range == '30') selected @endif>{{ trans('common.last_30_days') }}</option>
                                <option value="90" @if($range == '90') selected @endif>{{ trans('common.last_90_days') }}</option>
                                <option value="365" @if($range == '365') selected @endif>{{ trans('common.last_year') }}</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <x-ui.icon icon="chevron-down" class="h-4 w-4 text-secondary-400 group-hover:text-secondary-600 dark:group-hover:text-secondary-300 transition-colors" />
                            </div>
                        </div>
                        {{-- View Full List Button --}}
                        <a href="{{ route('admin.data.list', ['name' => 'activity-logs']) }}"
                            class="inline-flex items-center px-4 py-3 text-sm font-medium text-white bg-primary-600 rounded-xl hover:bg-primary-700 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5">
                            <x-ui.icon icon="list" class="w-4 h-4 mr-2" />
                            {{ trans('common.view_all_logs') }}
                        </a>
                    </div>
            </x-slot>
    </x-ui.page-header>

    {{-- Summary Metrics Cards - System Health KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mt-6">
            {{-- Total System Events - Deep Sapphire Blue --}}
            <div class="bg-white dark:bg-secondary-900 rounded-xl shadow-2xl shadow-gray-200/50 dark:shadow-none dark:border dark:border-secondary-800 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-[#0047AB]/10 dark:bg-[#0047AB]/20 rounded-xl">
                        <x-ui.icon icon="activity" class="w-6 h-6 text-[#0047AB] dark:text-blue-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-secondary-500 dark:text-secondary-400">{{ trans('common.total_activities') }}</p>
                        <p class="text-3xl font-extrabold text-[#0047AB] dark:text-white">
                            <span class="format-number">{{ $metrics['total'] }}</span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Events Today - Vivid Emerald (System Health) --}}
            <div class="bg-white dark:bg-secondary-900 rounded-xl shadow-2xl shadow-gray-200/50 dark:shadow-none dark:border dark:border-secondary-800 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
                            <x-ui.icon icon="zap" class="w-6 h-6 text-[#10B981] dark:text-emerald-400" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-secondary-500 dark:text-secondary-400">{{ trans('common.today') }}</p>
                            <p class="text-3xl font-extrabold text-secondary-900 dark:text-white">
                                <span class="format-number">{{ $metrics['today'] }}</span>
                            </p>
                        </div>
                    </div>
                    @include('admin.activity-logs.partials.diff-badge', ['diff' => $todayDiff])
                </div>
            </div>

            {{-- This Week - Deep Teal --}}
            <div class="bg-white dark:bg-secondary-900 rounded-xl shadow-2xl shadow-gray-200/50 dark:shadow-none dark:border dark:border-secondary-800 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-3 bg-teal-50 dark:bg-teal-900/20 rounded-xl">
                            <x-ui.icon icon="calendar-days" class="w-6 h-6 text-[#007A65] dark:text-teal-400" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-secondary-500 dark:text-secondary-400">{{ trans('common.this_week') }}</p>
                            <p class="text-3xl font-extrabold text-secondary-900 dark:text-white">
                                <span class="format-number">{{ $metrics['this_week'] }}</span>
                            </p>
                        </div>
                    </div>
                    @include('admin.activity-logs.partials.diff-badge', ['diff' => $weekDiff])
                </div>
            </div>

            {{-- This Month - Deep Plum/Violet --}}
            <div class="bg-white dark:bg-secondary-900 rounded-xl shadow-2xl shadow-gray-200/50 dark:shadow-none dark:border dark:border-secondary-800 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 p-3 bg-violet-50 dark:bg-violet-900/20 rounded-xl">
                            <x-ui.icon icon="users" class="w-6 h-6 text-[#7045AF] dark:text-violet-400" />
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-secondary-500 dark:text-secondary-400">{{ trans('common.this_month') }}</p>
                            <p class="text-3xl font-extrabold text-secondary-900 dark:text-white">
                                <span class="format-number">{{ $metrics['this_month'] }}</span>
                            </p>
                        </div>
                    </div>
                    @include('admin.activity-logs.partials.diff-badge', ['diff' => $monthDiff])
                </div>
            </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            {{-- Activity Timeline Chart --}}
            <div class="bg-white dark:bg-secondary-900 rounded-2xl shadow-sm border border-stone-200 dark:border-secondary-800 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-secondary-900 dark:text-white flex items-center">
                        <x-ui.icon icon="trending-up" class="w-5 h-5 mr-2 text-primary-500" />
                        {{ trans('common.activity_timeline') }}
                    </h3>
                    <span class="text-sm text-secondary-500 dark:text-secondary-400">
                        {{ trans('common.total') }}: <span class="format-number font-semibold">{{ $timeline['total'] }}</span>
                    </span>
                </div>
                <div id="activity-timeline-chart"
                    data-chart-type="line"
                    data-labels='@json($timeline['labels'])'
                    data-values='@json($timeline['values'])'
                    data-label="{{ trans('common.activities') }}"
                    data-height="280"></div>
            </div>

            {{-- Event Breakdown Donut --}}
            <div class="bg-white dark:bg-secondary-900 rounded-2xl shadow-sm border border-stone-200 dark:border-secondary-800 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-secondary-900 dark:text-white flex items-center">
                        <x-ui.icon icon="pie-chart" class="w-5 h-5 mr-2 text-primary-500" />
                        {{ trans('common.events_breakdown') }}
                    </h3>
                </div>
                <div id="event-breakdown-chart"
                    data-chart-type="donut"
                    data-labels='@json($eventBreakdown->keys())'
                    data-values='@json($eventBreakdown->values())'
                    data-colors='["#10b981","#f59e0b","#ef4444","#6366f1","#8b5cf6","#06b6d4"]'
                    data-total-label="{{ trans('common.total') }}"
                    data-height="280"></div>
            </div>
    </div>

    {{-- Second Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            {{-- Log Category Breakdown --}}
            <div class="bg-white dark:bg-secondary-900 rounded-2xl shadow-sm border border-stone-200 dark:border-secondary-800 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-secondary-900 dark:text-white flex items-center">
                        <x-ui.icon icon="folder" class="w-5 h-5 mr-2 text-primary-500" />
                        {{ trans('common.categories_breakdown') }}
                    </h3>
                </div>
                <div id="category-breakdown-chart"
                    data-chart-type="donut"
                    data-labels='@json($logNameBreakdown->keys())'
                    data-values='@json($logNameBreakdown->values())'
                    data-colors='["#3b82f6","#8b5cf6","#ec4899","#f97316","#14b8a6","#84cc16"]'
                    data-total-label="{{ trans('common.total') }}"
                    data-height="280"></div>
            </div>

            {{-- User Type Breakdown --}}
            <div class="bg-white dark:bg-secondary-900 rounded-2xl shadow-sm border border-stone-200 dark:border-secondary-800 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-secondary-900 dark:text-white flex items-center">
                        <x-ui.icon icon="users" class="w-5 h-5 mr-2 text-primary-500" />
                        {{ trans('common.user_types_breakdown') }}
                    </h3>
                </div>
                <div id="user-type-breakdown-chart"
                    data-chart-type="donut"
                    data-labels='@json($causerTypeBreakdown->keys())'
                    data-values='@json($causerTypeBreakdown->values())'
                    data-colors='["#6366f1","#10b981","#f59e0b","#ef4444","#8b5cf6"]'
                    data-total-label="{{ trans('common.total') }}"
                    data-height="280"></div>
            </div>
    </div>

    {{-- Bottom Row: Auth Stats + Most Active Users + Recent Activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
            {{-- Authentication Statistics --}}
            <div class="bg-white dark:bg-secondary-900 rounded-2xl shadow-sm border border-stone-200 dark:border-secondary-800 p-6">
                <h3 class="text-lg font-bold text-secondary-900 dark:text-white flex items-center mb-6">
                    <x-ui.icon icon="shield" class="w-5 h-5 mr-2 text-primary-500" />
                    {{ trans('common.authentication_stats') }}
                </h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl">
                        <div class="flex items-center">
                            <x-ui.icon icon="log-in" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 mr-3" />
                            <span class="text-sm font-medium text-secondary-700 dark:text-secondary-300">{{ trans('common.successful_logins') }}</span>
                        </div>
                        <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $authStats['logins'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-secondary-50 dark:bg-secondary-800 rounded-xl">
                        <div class="flex items-center">
                            <x-ui.icon icon="log-out" class="w-5 h-5 text-secondary-600 dark:text-secondary-400 mr-3" />
                            <span class="text-sm font-medium text-secondary-700 dark:text-secondary-300">{{ trans('common.logouts') }}</span>
                        </div>
                        <span class="text-lg font-bold text-secondary-600 dark:text-secondary-400">{{ $authStats['logouts'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-xl">
                        <div class="flex items-center">
                            <x-ui.icon icon="alert-triangle" class="w-5 h-5 text-red-600 dark:text-red-400 mr-3" />
                            <span class="text-sm font-medium text-secondary-700 dark:text-secondary-300">{{ trans('common.failed_logins') }}</span>
                        </div>
                        <span class="text-lg font-bold text-red-600 dark:text-red-400">{{ $authStats['failed_logins'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Most Active Users --}}
            <div class="bg-white dark:bg-secondary-900 rounded-2xl shadow-sm border border-stone-200 dark:border-secondary-800 p-6">
                <h3 class="text-lg font-bold text-secondary-900 dark:text-white flex items-center mb-6">
                    <x-ui.icon icon="trophy" class="w-5 h-5 mr-2 text-amber-500" />
                    {{ trans('common.most_active_users') }}
                </h3>
                <div class="space-y-3">
                    @forelse($mostActiveUsers->take(5) as $index => $user)
                        @php
                            $rankClass = match($index) {
                                0 => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                1 => 'bg-secondary-200 text-secondary-600 dark:bg-secondary-700 dark:text-secondary-300',
                                2 => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                default => 'bg-secondary-100 text-secondary-500 dark:bg-secondary-800 dark:text-secondary-400',
                            };
                        @endphp
                        <div class="flex items-center justify-between p-2 rounded-lg hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-colors">
                            <div class="flex items-center">
                                <span class="flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold {{ $rankClass }}">
                                    {{ $index + 1 }}
                                </span>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-secondary-900 dark:text-white">{{ $user['name'] }}</p>
                                    <p class="text-xs text-secondary-500 dark:text-secondary-400">{{ $user['type'] }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-secondary-700 dark:text-secondary-300 format-number">{{ $user['count'] }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 text-center py-4">{{ trans('common.no_data_available') }}</p>
                    @endforelse
                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="bg-white dark:bg-secondary-900 rounded-2xl shadow-sm border border-stone-200 dark:border-secondary-800 p-6">
                <h3 class="text-lg font-bold text-secondary-900 dark:text-white flex items-center mb-6">
                    <x-ui.icon icon="clock" class="w-5 h-5 mr-2 text-primary-500" />
                    {{ trans('common.recent_activity') }}
                </h3>
                <div class="space-y-3 max-h-80 overflow-y-auto">
                    @forelse($recentActivities->take(5) as $activity)
                        @php
                            $iconName = match($activity->event) {
                                'created' => 'plus',
                                'updated' => 'pencil',
                                'deleted' => 'trash',
                                'login' => 'log-in',
                                'logout' => 'log-out',
                                default => 'activity',
                            };
                            $bgClass = match($activity->event) {
                                'created' => 'bg-emerald-100 dark:bg-emerald-900/30',
                                'updated' => 'bg-amber-100 dark:bg-amber-900/30',
                                'deleted' => 'bg-red-100 dark:bg-red-900/30',
                                'login' => 'bg-primary-100 dark:bg-primary-900/30',
                                default => 'bg-secondary-100 dark:bg-secondary-800',
                            };
                            $iconClass = match($activity->event) {
                                'created' => 'text-emerald-600 dark:text-emerald-400',
                                'updated' => 'text-amber-600 dark:text-amber-400',
                                'deleted' => 'text-red-600 dark:text-red-400',
                                'login' => 'text-primary-600 dark:text-primary-400',
                                default => 'text-secondary-600 dark:text-secondary-400',
                            };
                        @endphp
                        <div class="flex items-start space-x-3 p-2 rounded-lg hover:bg-secondary-50 dark:hover:bg-secondary-800 transition-colors">
                            <div class="flex-shrink-0 mt-1">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full {{ $bgClass }}">
                                    <x-ui.icon :icon="$iconName" class="w-3 h-3 {{ $iconClass }}" />
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-secondary-900 dark:text-white truncate">{{ $activity->description }}</p>
                                <p class="text-xs text-secondary-500 dark:text-secondary-400">
                                    {{ $activity->causer?->name ?? $activity->causer?->email ?? 'System' }}
                                    &bull; {{ $activity->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-secondary-500 dark:text-secondary-400 text-center py-4">{{ trans('common.no_recent_activity') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Range selector script --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const rangeSelect = document.querySelector('#range');
        if (rangeSelect) {
            rangeSelect.addEventListener('change', () => {
                window.location.href = window.location.pathname + '?range=' + encodeURIComponent(rangeSelect.value);
            });
        }
    });
</script>
@stop

{{-- Charts initialize automatically via resources/js/analytics.js --}}

