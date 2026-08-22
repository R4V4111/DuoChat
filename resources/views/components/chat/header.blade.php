@props(['partner'])

<header class="flex items-center justify-between border-b border-[#E57373]/10 bg-white px-4 py-4 sm:px-6 sm:py-5" aria-label="Active conversation">
    <div class="flex items-center gap-4">
        <span class="flex size-12 items-center justify-center rounded-full bg-[#E57373] text-base font-semibold text-white ring-4 ring-[#E57373]/10">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($partner->name, 0, 1)) }}</span>
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#b75d5d]">Private conversation</p>
            <h1 class="mt-1 text-lg font-semibold tracking-tight text-slate-900">{{ $partner->name }}</h1>
            <div class="mt-1 flex items-center gap-1.5">
                {{-- Online indicator --}}
                <template x-if="partnerIsOnline">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="size-1.5 rounded-full bg-green-500" aria-hidden="true"></span>
                        <span class="text-xs text-green-600">Online</span>
                    </span>
                </template>
                {{-- Offline indicator --}}
                <template x-if="!partnerIsOnline">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="size-1.5 rounded-full bg-gray-400" aria-hidden="true"></span>
                        <span class="text-xs text-slate-400" x-text="partnerLastSeenDiff"></span>
                    </span>
                </template>
            </div>
        </div>
    </div>

    <button type="button" class="rounded-xl border border-stone-200 bg-stone-50 p-2.5 text-slate-500 transition hover:bg-stone-100 hover:text-slate-800" aria-label="Conversation details">
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="1" />
            <circle cx="19" cy="12" r="1" />
            <circle cx="5" cy="12" r="1" />
        </svg>
    </button>
</header>

