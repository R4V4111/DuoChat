@props(['content', 'sent' => false, 'time'])

<article @class([
    'flex max-w-[85%] flex-col gap-1 sm:max-w-[70%]',
    'ml-auto items-end' => $sent,
    'items-start' => ! $sent,
])>
    <div @class([
        'rounded-[18px] px-4 py-3 text-sm leading-6 shadow-sm',
        'rounded-br-md bg-[#E57373] text-white' => $sent,
        'rounded-bl-md bg-white text-slate-700' => ! $sent,
    ])>
        {{ $content }}
    </div>
    <time class="px-1 text-xs text-slate-400">{{ $time }}</time>
</article>
