@extends('member.layouts.default')

@section('page_title', trans('common.enter_code') . config('default.page_title_delimiter') . config('default.app_name'))

@section('content')
{{--
Enter Code Page - Consumer-Grade Simplicity

Staff gives you a code. You type it here. That's it.
Clean. Simple. Delightful.
--}}

<div class="min-h-screen relative">
    {{-- Ambient Background Glow --}}
    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-primary-500/10 dark:bg-primary-500/5 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-0 -left-40 w-96 h-96 bg-emerald-500/10 dark:bg-emerald-500/5 rounded-full blur-3xl animate-pulse" style="animation-delay: 1.5s;"></div>
    </div>

    <div class="space-y-6 max-w-xl mx-auto px-4 md:px-8 py-8 md:py-8">
        {{-- Back Button --}}
        <div class="animate-fade-in">
            <a href="{{ route('member.cards') }}" 
               class="inline-flex items-center gap-2 text-sm font-medium text-secondary-600 dark:text-secondary-400 hover:text-secondary-900 dark:hover:text-white transition-colors">
                <x-ui.icon icon="arrow-left" class="w-4 h-4" />
                <span>{{ trans('common.back') }}</span>
            </a>
        </div>

        {{-- Card Context (if available) --}}
        @if(isset($card))
            <div class="text-center animate-fade-in-up">
                <p class="text-sm text-secondary-600 dark:text-secondary-400">
                    {{ trans('common.redeeming_points_for') }}
                </p>
                <p class="text-lg font-semibold text-secondary-900 dark:text-white mt-1">
                    {{ $card->name }}
                </p>
            </div>
        @endif

        {{-- Header --}}
        <div class="text-center animate-fade-in-up" style="animation-delay: 50ms;">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 shadow-2xl shadow-primary-500/30 mb-6">
                <x-ui.icon icon="hash" class="w-10 h-10 text-white" />
            </div>
            <h1 class="text-4xl md:text-5xl font-bold text-secondary-900 dark:text-white mb-4 tracking-tight">
                {{ trans('common.enter_code') }}
            </h1>
            <p class="text-lg text-secondary-600 dark:text-secondary-400 max-w-md mx-auto">
                {{ trans('common.enter_code_description') }}
            </p>
        </div>

        {{-- Messages --}}
        <x-forms.messages class="animate-fade-in-up" style="animation-delay: 100ms;" />

        {{-- Code Input Card --}}
        <div class="animate-fade-in-up" style="animation-delay: 150ms;">
            <div class="bg-white dark:bg-secondary-900 rounded-xl border border-stone-200 dark:border-secondary-800 shadow-sm p-8 md:p-12">
                <x-forms.form-open action="{{ route('member.code.enter.post') }}" method="POST" />

                {{-- Code Input --}}
                <div class="mb-6">
                    <label for="code" class="block text-sm font-semibold text-secondary-700 dark:text-secondary-300 mb-4 text-center">
                        {{ trans('common.four_digit_code') }}
                    </label>
                    <input
                        type="text"
                        id="code"
                        name="code"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="4"
                        placeholder="••••"
                        required
                        autofocus
                        class="w-full text-center text-4xl md:text-5xl font-bold font-mono tracking-[0.5em] 
                               bg-secondary-50 dark:bg-secondary-800 
                               border-2 border-secondary-200 dark:border-secondary-700 
                               rounded-2xl px-6 py-5 
                               text-secondary-900 dark:text-white 
                               placeholder:text-secondary-300 dark:placeholder:text-secondary-600
                               focus:outline-none focus:ring-4 focus:ring-primary-500/20 dark:focus:ring-primary-400/20 
                               focus:border-primary-500 dark:focus:border-primary-500
                               transition-all duration-300"
                    />
                </div>

                {{-- Submit Button --}}
                <button type="submit"
                    class="w-full py-4 px-6 text-white bg-gradient-to-br from-primary-500 to-primary-600 hover:from-primary-400 hover:to-primary-500 focus:ring-4 focus:ring-primary-300 dark:focus:ring-primary-800 font-bold rounded-xl text-lg shadow-lg shadow-primary-500/25 hover:shadow-xl hover:shadow-primary-500/30 hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2">
                    <x-ui.icon icon="check-circle" class="w-5 h-5" />
                    {{ trans('common.submit_code') }}
                </button>

                <x-forms.form-close />
            </div>
        </div>

        {{-- Help Text --}}
        <div class="flex items-start gap-4 p-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl animate-fade-in-up" style="animation-delay: 200ms;">
            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                <x-ui.icon icon="lightbulb" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
            </div>
            <div>
                <p class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-1">
                    {{ trans('common.where_to_find_code') }}
                </p>
                <p class="text-sm text-blue-700 dark:text-blue-300">
                    {{ trans('common.enter_code_help', ['expiry' => Carbon\CarbonInterval::minutes(config('default.code_to_redeem_points_valid_minutes'))->cascade()->forHumans(['parts' => 2])]) }}
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('code');
    if (!input) return;
    
    // Auto-format and validate input
    input.addEventListener('input', (e) => {
        // Only allow numbers
        let value = e.target.value.replace(/[^0-9]/g, '');
        // Limit to 4 digits
        value = value.slice(0, 4);
        e.target.value = value;
    });
    
    // Auto-focus on page load
    input.focus();
});
</script>
@stop
