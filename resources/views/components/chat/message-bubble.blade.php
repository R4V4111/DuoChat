@props(['content', 'sent' => false, 'time', 'readAt' => null])

<article @class([
    'flex max-w-[85%] flex-col gap-1.5 sm:max-w-[70%]',
    'ml-auto items-end' => $sent,
    'items-start' => ! $sent,
])>
    <div @class([
        'rounded-[18px] px-4 py-3 text-sm leading-6',
        'rounded-br-md bg-[#E57373] text-white shadow-md shadow-[#E57373]/20' => $sent,
        'rounded-bl-md border border-rose-100 bg-white text-slate-700 shadow-sm' => ! $sent,
    ])>
        {{ $content }}
    </div>
    <div @class([
        'flex items-center gap-1 px-1 text-xs',
        'justify-end' => $sent,
        'justify-start' => ! $sent,
    ])>
        <time @class([
            'text-[#c96767]' => $sent,
            'text-slate-400' => ! $sent,
        ])>{{ $time }}</time>
        @if ($sent)
            @if ($readAt)
                <!-- Double check - read -->
                <svg class="size-3.5 text-[#c96767]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-label="Read">
                    <polyline points="20 6 9 17 4 12" />
                    <polyline points="14 6 3 17 -2 12" />
                </svg>
            @else
                <!-- Single check - sent/delivered -->
                <svg class="size-3.5 text-[#c96767]/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-label="Delivered">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
            @endif
        @endif
    </div>
</article>
