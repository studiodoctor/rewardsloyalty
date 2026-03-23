@extends('member.layouts.default')

@section('page_title', trans('common.generate_request_link') . config('default.page_title_delimiter') . config('default.app_name'))

@section('content')
<div class="min-h-screen relative">
    {{-- Ambient Background --}}
    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-violet-500/10 dark:bg-violet-500/5 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-0 -left-40 w-96 h-96 bg-primary-500/10 dark:bg-primary-500/5 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
    </div>

    <div class="space-y-6 max-w-xl mx-auto px-4 md:px-8 py-8 md:py-8">
        {{-- Back Button --}}
        <div class="animate-fade-in">
            <a href="{{ url()->previous() }}" 
               class="inline-flex items-center gap-2 text-sm font-medium text-secondary-600 dark:text-secondary-400 hover:text-secondary-900 dark:hover:text-white transition-colors">
                <x-ui.icon icon="arrow-left" class="w-4 h-4" />
                <span>{{ trans('common.back') }}</span>
            </a>
        </div>

        {{-- Card Context --}}
        @if($selectedCard)
            <div class="text-center animate-fade-in-up" style="animation-delay: 50ms;">
                <p class="text-sm text-secondary-600 dark:text-secondary-400">
                    {{ trans('common.generating_request_for') }}
                </p>
                <p class="text-lg font-semibold text-secondary-900 dark:text-white mt-1">
                    {{ $selectedCard->name }}
                </p>
            </div>
        @endif

        {{-- Header --}}
        <div class="text-center animate-fade-in-up" style="animation-delay: 100ms;">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-violet-500 to-violet-600 shadow-2xl shadow-violet-500/30 mb-6">
                <x-ui.icon icon="send" class="w-10 h-10 text-white" />
            </div>
            <h1 class="text-4xl md:text-5xl font-bold text-secondary-900 dark:text-white mb-4 tracking-tight">
                {{ trans('common.generate_request_link') }}
            </h1>
            <p class="text-lg text-secondary-600 dark:text-secondary-400 max-w-md mx-auto">
                {{ trans('common.request_link_subtitle') }}
            </p>
        </div>

        {{-- Messages --}}
        <x-forms.messages class="animate-fade-in-up" style="animation-delay: 150ms;" />

        {{-- Form Card --}}
        <div class="animate-fade-in-up" style="animation-delay: 200ms;">
            <div class="bg-white dark:bg-secondary-900 rounded-xl border border-stone-200 dark:border-secondary-800 shadow-sm p-6 md:p-8">
                <x-forms.form-open action="{{ route('member.request.points.generate.post') }}" method="POST" />
                @csrf

                @if(!$selectedCard)
                    <div class="mb-6">
                        <x-forms.select 
                            name="card_id" 
                            :label="trans('common.select_card')" 
                            :options="$options"
                            placeholder="{{ trans('common.select_card_placeholder') }}" 
                            value="wildcard" 
                            required 
                        />
                    </div>
                @endif

                <button type="submit"
                    class="w-full py-4 px-6 text-white bg-gradient-to-br from-violet-500 to-violet-600 hover:from-violet-400 hover:to-violet-500 focus:ring-4 focus:ring-violet-300 font-bold rounded-xl text-lg shadow-lg shadow-violet-500/25 hover:shadow-xl hover:shadow-violet-500/30 hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2">
                    <x-ui.icon icon="link" class="w-5 h-5" />
                    {{ trans('common.generate_link') }}
                </button>
                
                <x-forms.form-close />
            </div>
        </div>

        {{-- Info Box --}}
        <div class="flex items-start gap-4 p-6 bg-violet-50 dark:bg-violet-900/20 border border-violet-200 dark:border-violet-800 rounded-2xl animate-fade-in-up" style="animation-delay: 250ms;">
            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/40 flex items-center justify-center">
                <x-ui.icon icon="info" class="w-5 h-5 text-violet-600 dark:text-violet-400" />
            </div>
            <div>
                <p class="text-sm font-semibold text-violet-900 dark:text-violet-100 mb-1">
                    {{ trans('common.how_it_works') }}
                </p>
                <p class="text-sm text-violet-700 dark:text-violet-300">
                    {{ trans('common.request_link_info') }}
                </p>
            </div>
        </div>
    </div>
</div>
@stop
