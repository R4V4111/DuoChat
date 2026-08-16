@props(['partner'])

<div class="flex h-screen flex-col bg-white p-4">
    <div class="rounded-2xl bg-[#E57373]/10 p-4">
        <a href="{{ url('/') }}" class="flex items-center gap-3">
            <span class="flex size-10 items-center justify-center rounded-xl bg-[#E57373] text-sm font-bold text-white shadow-sm shadow-[#E57373]/30">D</span>
            <span>
                <span class="block text-lg font-bold tracking-tight text-slate-900">DuoChat</span>
                <span class="block text-xs font-medium text-[#b75d5d]">A private space for two</span>
            </span>
        </a>
    </div>

    <nav class="mt-8" aria-label="Conversations">
        <p class="px-2 text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Your conversation</p>

        <a href="{{ route('chat') }}" class="mt-3 flex w-full items-center gap-3 rounded-2xl border border-[#E57373]/15 bg-[#E57373]/10 p-3 text-left shadow-sm shadow-[#E57373]/10">
            <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-[#E57373] text-sm font-semibold text-white ring-4 ring-white">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($partner->name, 0, 1)) }}</span>
            <span class="min-w-0 flex-1">
                <span class="block truncate font-semibold text-slate-900">{{ $partner->name }}</span>
                <span class="mt-1 block truncate text-sm text-[#b75d5d]">Private conversation</span>
            </span>
            <span class="size-2 shrink-0 rounded-full bg-[#E57373]" aria-hidden="true"></span>
        </a>
    </nav>

    <div class="mt-auto rounded-2xl border border-stone-100 bg-stone-50 p-4">
        <div class="flex items-center gap-2 text-[#b75d5d]">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.9-8.6a5.5 5.5 0 0 0-.1-7.8Z" />
            </svg>
            <p class="text-sm font-semibold text-slate-700">Just the two of you</p>
        </div>
        <p class="mt-2 text-xs leading-5 text-slate-500">Your conversation stays simple, personal, and close.</p>
    </div>
</div>
