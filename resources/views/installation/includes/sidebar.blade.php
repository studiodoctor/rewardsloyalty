{{--
Installation Sidebar - Lines connect directly to circles
--}}
<div class="flex flex-col h-full" x-data="{ darkMode: localStorage.getItem('color-theme') === 'dark' }">
    {{-- Logo --}}
    <div class="mb-10">
        <a href="{{ route('redir.locale') }}" class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-md bg-white/10 flex items-center justify-center text-white font-semibold text-xs">
                {{ substr(config('default.app_name'), 0, 1) }}
            </div>
            <span class="font-semibold text-base text-white">{{ config('default.app_name') }}</span>
        </a>
    </div>

    {{-- Stepper --}}
    <nav class="flex-1">
        <div class="space-y-0">
            {{-- Step 1 --}}
            <div class="flex items-start gap-3">
                <div class="flex flex-col items-center">
                    <button type="button" @click="tab >= 1 ? tab = 1 : null"
                        class="w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-semibold transition-all shrink-0"
                        :class="{
                            'bg-white text-primary-900': tab === 1,
                            'bg-emerald-500 text-white': tab > 1,
                            'bg-primary-900 border border-white/20 text-white/50': tab < 1
                        }">
                        <span x-show="tab <= 1">1</span>
                        <x-ui.icon icon="check" class="w-3 h-3" x-show="tab > 1" x-cloak />
                    </button>
                    {{-- Line connects directly to circle bottom (no margin) --}}
                    <div class="w-px h-6 transition-colors duration-300"
                        :class="tab > 1 ? 'bg-emerald-400/60' : 'bg-white/10'"></div>
                </div>
                <button type="button" @click="tab >= 1 ? tab = 1 : null"
                    class="text-sm pt-0.5"
                    :class="tab === 1 ? 'text-white font-medium' : 'text-white/50'">Requirements</button>
            </div>

            {{-- Step 2 --}}
            <div class="flex items-start gap-3">
                <div class="flex flex-col items-center">
                    <button type="button" @click="tab >= 2 ? tab = 2 : null"
                        class="w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-semibold transition-all shrink-0"
                        :class="{
                            'bg-white text-primary-900': tab === 2,
                            'bg-emerald-500 text-white': tab > 2,
                            'bg-primary-900 border border-white/20 text-white/50': tab < 2
                        }"
                        :disabled="tab < 2">
                        <span x-show="tab <= 2">2</span>
                        <x-ui.icon icon="check" class="w-3 h-3" x-show="tab > 2" x-cloak />
                    </button>
                    <div class="w-px h-6 transition-colors duration-300"
                        :class="tab > 2 ? 'bg-emerald-400/60' : 'bg-white/10'"></div>
                </div>
                <button type="button" @click="tab >= 2 ? tab = 2 : null"
                    class="text-sm pt-0.5"
                    :class="tab === 2 ? 'text-white font-medium' : (tab < 2 ? 'text-white/30' : 'text-white/50')">Configuration</button>
            </div>

            {{-- Step 3 --}}
            <div class="flex items-start gap-3">
                <div class="flex flex-col items-center">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-semibold transition-all shrink-0"
                        :class="{
                            'bg-white text-primary-900': tab === 3,
                            'bg-primary-900 border border-white/20 text-white/50': tab < 3
                        }">
                        <span>3</span>
                    </div>
                </div>
                <span class="text-sm pt-0.5"
                    :class="tab === 3 ? 'text-white font-medium' : 'text-white/30'">Install</span>
            </div>
        </div>
    </nav>

    {{-- Footer --}}
    <div class="mt-auto pt-6 border-t border-white/10 space-y-3">
        <button type="button" @click="toggleTheme(); darkMode = !darkMode"
            class="flex items-center gap-2 text-white/40 hover:text-white/70 text-xs transition-colors">
            <x-ui.icon icon="sun" class="w-3.5 h-3.5" x-show="darkMode" x-cloak />
            <x-ui.icon icon="moon" class="w-3.5 h-3.5" x-show="!darkMode" />
            <span x-text="darkMode ? 'Light Mode' : 'Dark Mode'"></span>
        </button>
        <div class="text-[10px] text-white/20">v{{ config('version.current') }}</div>
    </div>
</div>