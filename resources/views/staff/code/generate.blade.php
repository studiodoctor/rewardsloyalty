@extends('staff.layouts.default')

@section('page_title', $card->head . config('default.page_title_delimiter') . trans('common.generate_code') . config('default.page_title_delimiter') . config('default.app_name'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950">
    {{-- Ambient background effects --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/3 right-1/4 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl animate-pulse-slow"></div>
        <div class="absolute bottom-1/3 left-1/4 w-80 h-80 bg-orange-500/10 rounded-full blur-3xl animate-pulse-slow" style="animation-delay: 1.5s;"></div>
    </div>

    <div class="relative z-10 w-full max-w-lg mx-auto px-4 py-8 md:py-12">
        {{-- Header --}}
        <div class="animate-fade-in" style="animation-delay: 50ms;">
            <x-ui.page-header
                icon="ticket"
                :title="trans('common.generate_code')"
                description="Create a redemption code for members"
                compact
            />
        </div>

        {{-- Card Display --}}
        <div class="mb-6 animate-fade-in-up" style="animation-delay: 100ms;">
            <x-member.card
                :card="$card"
                :member="null"
                :flippable="false"
                :links="false"
                :show-qr="false"
            />
        </div>

        {{-- Messages --}}
        <div class="mb-6 animate-fade-in-up" style="animation-delay: 125ms;">
            <x-forms.messages />
        </div>

        {{-- Generate Code Form --}}
        <div class="animate-fade-in-up" style="animation-delay: 150ms;">
            <x-forms.form-open
                action="{{ route('staff.code.generate.post', ['card_identifier' => $card->unique_identifier]) }}"
                method="POST"
            />
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-lg overflow-hidden">
                    
                    {{-- Form Header --}}
                    <div class="px-6 py-4 border-b border-slate-200/50 dark:border-slate-700/50 bg-slate-50/50 dark:bg-slate-900/50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-100 to-orange-200 dark:from-amber-900/50 dark:to-orange-800/50 flex items-center justify-center">
                                <x-ui.icon icon="coins" class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div>
                                <h3 class="font-semibold text-slate-900 dark:text-white">{{ trans('common.points') }}</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $card->min_points_per_purchase }} - {{ $card->max_points_per_purchase }} points</p>
                            </div>
                        </div>
                    </div>

                    {{-- Form Content --}}
                    <div class="p-6 space-y-5">
                        <x-forms.input
                            name="points"
                            :label="trans('common.points')"
                            type="number"
                            inputmode="numeric"
                            icon="coins"
                            affix-class="text-slate-400 dark:text-slate-500 text-lg"
                            input-class="text-lg"
                            :min="$card->min_points_per_purchase"
                            :max="$card->max_points_per_purchase"
                            step="1"
                            :placeholder="trans('common.points_placeholder', ['min' => $card->min_points_per_purchase, 'max' => $card->max_points_per_purchase])"
                            required
                        />
                    </div>

                    {{-- Submit Button --}}
                    <div class="p-6 pt-0">
                        <button type="submit" class="relative group/btn w-full">
                            {{-- Button glow --}}
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-amber-500 via-orange-500 to-amber-500 rounded-2xl opacity-0 group-hover/btn:opacity-75 blur-md transition-all duration-300"></div>
                            
                            {{-- Button content --}}
                            <div class="relative flex items-center justify-center gap-3 px-8 py-4 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-white font-semibold rounded-2xl shadow-lg shadow-amber-900/20 transition-all duration-300 group-hover/btn:shadow-xl group-hover/btn:shadow-amber-500/30">
                                <x-ui.icon icon="sparkles" class="w-5 h-5 transition-transform group-hover/btn:scale-110" />
                                <span>{{ trans('common.generate_code') }}</span>
                                <x-ui.icon icon="arrow-right" class="w-5 h-5 transition-transform group-hover/btn:translate-x-1" />
                            </div>
                        </button>
                    </div>
                </div>
            <x-forms.form-close />
        </div>

        {{-- Info Cards --}}
        <div class="mt-6 space-y-3 animate-fade-in-up" style="animation-delay: 200ms;">
            {{-- How it works --}}
            <div class="relative overflow-hidden bg-white dark:bg-slate-800/80 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                <div class="flex gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <x-ui.icon icon="info" class="w-4.5 h-4.5 text-amber-600 dark:text-amber-400"/>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-medium text-slate-900 dark:text-white text-sm mb-0.5">{{ trans('common.how_it_works') }}</h4>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            {{ trans('common.generate_code_info', ['expiry' => Carbon\CarbonInterval::minutes(config('default.code_to_redeem_points_valid_minutes'))->cascade()->forHumans(['parts' => 2])]) }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Tier Multiplier Info --}}
            <div class="relative overflow-hidden bg-white dark:bg-slate-800/80 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                <div class="flex gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                            <x-ui.icon icon="trending-up" class="w-4.5 h-4.5 text-emerald-600 dark:text-emerald-400"/>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-medium text-slate-900 dark:text-white text-sm mb-0.5">{{ trans('common.tier_multiplier_applied') }}</h4>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            {{ trans('common.tier_multiplier_code_info') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Points validation script --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('points');
    if (!input) return;

    input.addEventListener('input', () => {
        input.value = input.value.replace(/[^0-9]/g, '').slice(0, {{ strlen($card->max_points_per_purchase) }});
    });
});
</script>

<style>
    @keyframes fade-in {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes fade-in-up {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulse-slow {
        0%, 100% { opacity: 0.4; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(1.05); }
    }
    .animate-fade-in {
        animation: fade-in 0.6s ease-out forwards;
    }
    .animate-fade-in-up {
        animation: fade-in-up 0.6s ease-out forwards;
        opacity: 0;
    }
    .animate-pulse-slow {
        animation: pulse-slow 4s ease-in-out infinite;
    }
</style>
@stop
