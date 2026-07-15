@props(['partner'])

<div class="flex h-screen flex-col p-4">
    <div class="flex items-center justify-between px-2 py-3">
        <a href="{{ url('/') }}" class="text-xl font-bold tracking-tight text-slate-900">DuoChat</a>
        <button type="button" class="rounded-xl p-2 text-slate-500 transition hover:bg-stone-100 hover:text-slate-800" aria-label="Search conversations">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.35-4.35" />
            </svg>
        </button>
    </div>

    <nav class="mt-6 space-y-2" aria-label="Conversations">
        <a href="{{ route('chat') }}" class="flex w-full items-center gap-3 rounded-2xl bg-[#E57373]/10 p-4 text-left">
            <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-[#E57373] text-sm font-semibold text-white">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($partner->name, 0, 1)) }}</span>
            <span class="min-w-0 flex-1">
                <span class="block truncate font-semibold text-slate-900">{{ $partner->name }}</span>
                <span class="mt-1 block truncate text-sm text-slate-500">Private conversation</span>
            </span>
        </a>
    </nav>

    <div class="mt-auto rounded-2xl bg-stone-100 p-4">
        <p class="text-sm font-medium text-slate-700">Private chat</p>
        <p class="mt-1 text-xs leading-5 text-slate-500">Messages are shown in the active conversation.</p>
    </div>
</div>
