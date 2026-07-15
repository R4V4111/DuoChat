<form method="POST" action="{{ route('chat.send') }}" class="border-t border-stone-200 bg-white p-4 sm:px-6" aria-label="Message composer">
    @csrf

    <div class="flex items-end gap-3 rounded-2xl border border-stone-200 bg-stone-50 p-2 focus-within:border-[#E57373] focus-within:ring-4 focus-within:ring-[#E57373]/10">
        <label for="message" class="sr-only">Message</label>
        <textarea id="message" name="body" rows="1" placeholder="Write a message..." class="max-h-32 min-h-10 flex-1 resize-none border-0 bg-transparent px-2 py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:ring-0">{{ old('body') }}</textarea>
        <button type="submit" class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-[#E57373] text-white transition hover:bg-[#D96767] focus:outline-none focus:ring-4 focus:ring-[#E57373]/30" aria-label="Send message">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="m22 2-7 20-4-9-9-4Z" />
                <path d="M22 2 11 13" />
            </svg>
        </button>
    </div>

    @error('body')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</form>
