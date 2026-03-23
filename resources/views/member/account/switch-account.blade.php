{{--
Switch Account Tab
Enables members to link their device to a different member account by entering a member code.
Particularly useful for anonymous members using multiple devices.

Design: Inspired by Stampify's device sync pattern.
Simple code input with confirmation step for safety.
--}}

@php
    $currentMember = auth('member')->user();
    $currentCode = $currentMember->device_code ?? '—';
    $isAnonymous = $currentMember->isAnonymous();
    
    // Get the configured code length from settings
    $codeLength = config('settings.member_unique_identifier_length', 6);
@endphp

<div class="space-y-6" 
     x-data="switchAccountManager({
         csrfToken: '{{ csrf_token() }}',
         switchUrl: '{{ route('member.account.switch') }}',
         currentCode: '{{ $currentCode }}',
         codeLength: {{ $codeLength }},
     })">
    
    {{-- Current Account Info --}}
    <section class="space-y-4">
        {{-- Section Header --}}
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center shadow-lg shadow-primary-500/20">
                <x-ui.icon icon="user-circle" class="w-5 h-5 text-white" />
            </div>
            <div>
                <h3 class="text-base font-semibold text-secondary-900 dark:text-white">
                    {{ trans('common.switch_account.current_account') }}
                </h3>
                <p class="text-sm text-secondary-500 dark:text-secondary-400">
                    {{ trans('common.switch_account.current_account_description') }}
                </p>
            </div>
        </div>
        
        {{-- Current Member Card - Redesigned to show code prominently --}}
        <div class="bg-stone-50 dark:bg-secondary-800/50 rounded-xl p-5 border border-stone-200 dark:border-secondary-700/50">
            <div class="flex items-center gap-4">
                {{-- Avatar --}}
                <div class="flex-shrink-0">
                    @if($currentMember->avatar)
                        <img src="{{ $currentMember->{'avatar-small'} ?? $currentMember->avatar }}" 
                             alt="{{ $currentMember->name }}"
                             class="w-14 h-14 rounded-xl object-cover ring-2 ring-white dark:ring-secondary-700 shadow-sm" />
                    @else
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 
                                    flex items-center justify-center text-white font-semibold text-xl
                                    ring-2 ring-white dark:ring-secondary-700 shadow-sm">
                            {{ strtoupper(substr($currentMember->name ?? 'M', 0, 1)) }}
                        </div>
                    @endif
                </div>
                
                {{-- Info with code prominently displayed --}}
                <div class="flex-1">
                    {{-- Show the code as the primary identifier --}}
                    <p class="font-mono text-2xl font-bold text-secondary-900 dark:text-white tracking-wider mb-1">
                        {{ $currentCode }}
                    </p>
                    @if($currentMember->name && $currentMember->name !== $currentCode)
                        <p class="text-sm text-secondary-500 dark:text-secondary-400">
                            {{ $currentMember->name }}
                        </p>
                    @endif
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 mt-1 text-xs font-medium 
                                 bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 rounded-full">
                        <x-ui.icon icon="user" class="w-3 h-3" />
                        {{ trans('common.switch_account.anonymous_member') }}
                    </span>
                </div>
            </div>
            
            {{-- Helpful reminder about the code --}}
            <div class="mt-4 p-3 rounded-lg bg-stone-100 dark:bg-secondary-700/50 border border-stone-200/50 dark:border-secondary-600/50">
                <p class="text-sm text-secondary-600 dark:text-secondary-400">
                    <x-ui.icon icon="info" class="w-4 h-4 inline -mt-0.5 mr-1" />
                    {{ trans('common.switch_account.code_hint') }}
                </p>
            </div>
        </div>
    </section>
    
    {{-- Divider --}}
    <div class="border-t border-stone-200 dark:border-secondary-700/50"></div>
    
    {{-- Switch Account Section --}}
    <section class="space-y-4">
        {{-- Section Header --}}
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                <x-ui.icon icon="repeat" class="w-5 h-5 text-white" />
            </div>
            <div>
                <h3 class="text-base font-semibold text-secondary-900 dark:text-white">
                    {{ trans('common.switch_account.switch_title') }}
                </h3>
                <p class="text-sm text-secondary-500 dark:text-secondary-400">
                    {{ trans('common.switch_account.switch_description') }}
                </p>
            </div>
        </div>
        
        {{-- Switch Form --}}
        <div class="bg-stone-50 dark:bg-secondary-800/50 rounded-xl p-5 border border-stone-200 dark:border-secondary-700/50">
            
            {{-- Use Cases Info --}}
            <div class="mb-5 p-4 rounded-xl bg-blue-50 dark:bg-blue-500/10 border border-blue-200/50 dark:border-blue-500/20">
                <div class="flex gap-3">
                    <x-ui.icon icon="info" class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                    <div class="text-sm text-blue-700 dark:text-blue-300/90 space-y-1">
                        <p class="font-medium">{{ trans('common.switch_account.when_to_use') }}</p>
                        <ul class="list-disc list-inside space-y-0.5 opacity-90">
                            <li>{{ trans('common.switch_account.use_case_1') }}</li>
                            <li>{{ trans('common.switch_account.use_case_2') }}</li>
                            <li>{{ trans('common.switch_account.use_case_3') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            {{-- Code Input --}}
            <div class="mb-4">
                <label for="member_code" class="block text-sm font-medium text-secondary-700 dark:text-secondary-300 mb-2">
                    {{ trans('common.switch_account.enter_code') }}
                </label>
                <input 
                    type="text"
                    id="member_code"
                    x-model="newCode"
                    @input="newCode = newCode.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, codeLength); resetState()"
                    :maxlength="codeLength"
                    class="w-full px-4 py-4 rounded-xl text-center font-mono text-2xl font-bold tracking-[0.15em] uppercase
                           bg-white dark:bg-secondary-900
                           border-2 text-secondary-900 dark:text-white
                           placeholder-secondary-300 dark:placeholder-secondary-600
                           outline-none transition-all"
                    :class="error 
                        ? 'border-red-400 dark:border-red-500 focus:border-red-500 focus:ring-red-500/20' 
                        : 'border-stone-200 dark:border-secondary-700 focus:border-primary-500 dark:focus:border-primary-400 focus:ring-primary-500/20'"
                    :placeholder="'X'.repeat(codeLength)"
                />
            </div>
            
            {{-- Error Message --}}
            <div x-show="error" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="mb-4 p-3 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200/50 dark:border-red-500/20">
                <div class="flex gap-2">
                    <x-ui.icon icon="x-circle" class="w-4 h-4 text-red-500 dark:text-red-400 flex-shrink-0 mt-0.5" />
                    <p class="text-sm text-red-600 dark:text-red-300/90" x-text="error"></p>
                </div>
            </div>
            
            {{-- Same Code Warning --}}
            <div x-show="newCode === currentCode && newCode.length === codeLength" 
                 x-cloak
                 class="mb-4 p-3 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200/50 dark:border-amber-500/20">
                <div class="flex gap-2">
                    <x-ui.icon icon="alert-circle" class="w-4 h-4 text-amber-500 dark:text-amber-400 flex-shrink-0 mt-0.5" />
                    <p class="text-sm text-amber-600 dark:text-amber-300/90">
                        {{ trans('common.switch_account.same_code_warning') }}
                    </p>
                </div>
            </div>
            
            {{-- Warning about current account --}}
            <div x-show="newCode.length === codeLength && newCode !== currentCode && !confirming" 
                 x-cloak
                 class="mb-4 p-3 rounded-xl bg-stone-100 dark:bg-secondary-700/50 border border-stone-200 dark:border-secondary-600/50">
                <div class="flex gap-2">
                    <x-ui.icon icon="alert-triangle" class="w-4 h-4 text-secondary-500 dark:text-secondary-400 flex-shrink-0 mt-0.5" />
                    <p class="text-sm text-secondary-600 dark:text-secondary-300">
                        {{ trans('common.switch_account.switch_warning') }}
                    </p>
                </div>
            </div>
            
            {{-- Action Button --}}
            <button 
                type="button"
                @click="handleSwitch()"
                :disabled="loading || newCode.length !== codeLength || newCode === currentCode"
                class="w-full py-3.5 px-4 rounded-xl font-medium text-white
                       transition-all duration-200 active:scale-[0.98]
                       disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100"
                :class="confirming 
                    ? 'bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-lg shadow-amber-500/25' 
                    : 'bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 shadow-lg shadow-primary-500/25'"
            >
                {{-- Loading state --}}
                <span x-show="loading" class="flex items-center justify-center gap-2">
                    <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ trans('common.switch_account.switching') }}
                </span>
                
                {{-- Confirm state (second click) --}}
                <span x-show="!loading && confirming" class="flex items-center justify-center gap-2">
                    <x-ui.icon icon="alert-circle" class="w-5 h-5" />
                    {{ trans('common.switch_account.confirm_switch') }}
                </span>
                
                {{-- Initial state --}}
                <span x-show="!loading && !confirming">
                    {{ trans('common.switch_account.switch_button') }}
                </span>
            </button>
            
            {{-- Cancel confirmation --}}
            <button 
                x-show="confirming && !loading"
                x-cloak
                type="button"
                @click="confirming = false"
                class="w-full mt-2 py-2 text-sm text-secondary-500 dark:text-secondary-400 hover:text-secondary-700 dark:hover:text-secondary-300 transition-colors"
            >
                {{ trans('common.cancel') }}
            </button>
        </div>
    </section>
</div>

{{-- Alpine.js Component --}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('switchAccountManager', (config) => ({
        csrfToken: config.csrfToken,
        switchUrl: config.switchUrl,
        currentCode: config.currentCode,
        codeLength: config.codeLength,
        newCode: '',
        loading: false,
        confirming: false,
        error: '',
        
        resetState() {
            this.confirming = false;
            this.error = '';
        },
        
        async handleSwitch() {
            // First click: show confirmation
            if (!this.confirming) {
                this.confirming = true;
                return;
            }
            
            // Second click: perform switch
            this.loading = true;
            this.error = '';
            
            try {
                const response = await fetch(this.switchUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        code: this.newCode.toUpperCase() 
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Update the device UUID cookie if provided
                    if (data.device_uuid) {
                        document.cookie = `member_device_uuid=${data.device_uuid}; path=/; max-age=31536000; SameSite=Lax`;
                    }
                    
                    // Show success message
                    if (window.appSuccess) {
                        window.appSuccess(data.message || '{{ trans('common.switch_account.success') }}');
                    }
                    
                    // Redirect to my-cards (can't reload - the profile URL has old member UUID)
                    setTimeout(() => {
                        window.location.href = '{{ route('member.cards') }}';
                    }, 800);
                } else {
                    this.error = data.message || '{{ trans('common.switch_account.code_not_found') }}';
                    this.confirming = false;
                }
            } catch (e) {
                console.error('Switch account error:', e);
                this.error = '{{ trans('common.error_occurred') }}';
                this.confirming = false;
            } finally {
                this.loading = false;
            }
        }
    }));
});
</script>
