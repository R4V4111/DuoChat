@props(['content', 'sent' => false, 'time'])

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
    <time @class([
        'px-1 text-xs',
        'text-[#c96767]' => $sent,
        'text-slate-400' => ! $sent,
    ])>{{ $time }}</time>
</article>
