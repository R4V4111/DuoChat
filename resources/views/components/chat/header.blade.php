@props(['partner'])

<header class="flex items-center justify-between border-b border-stone-200 bg-white px-4 py-4 sm:px-6" aria-label="Active conversation">
    <div class="flex items-center gap-3">
        <span class="flex size-10 items-center justify-center rounded-full bg-[#E57373] text-sm font-semibold text-white">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($partner->name, 0, 1)) }}</span>
        <div>
            <h1 class="font-semibold text-slate-900">{{ $partner->name }}</h1>
            <p class="text-sm text-slate-500">Private conversation</p>
        </div>
    </div>

    <button type="button" class="rounded-xl p-2 text-slate-500 transition hover:bg-stone-100 hover:text-slate-800" aria-label="Conversation details">
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="12" r="1" />
            <circle cx="19" cy="12" r="1" />
            <circle cx="5" cy="12" r="1" />
        </svg>
    </button>
</header>
