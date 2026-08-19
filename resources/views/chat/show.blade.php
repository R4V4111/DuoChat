<x-chat-layout>
    <x-slot:sidebar>
        <x-chat.sidebar :partner="$partner" />
    </x-slot:sidebar>

    <section class="flex min-h-screen min-w-0 flex-1 flex-col bg-[#fffaf9] sm:!h-screen">
        <x-chat.header :partner="$partner" />

        <main class="flex-1 space-y-6 overflow-y-auto px-4 py-6 sm:px-6 sm:py-8 lg:px-8" aria-label="Chat messages">
            <p class="mx-auto w-fit rounded-full border border-[#E57373]/15 bg-white px-3 py-1 text-xs font-medium text-[#b75d5d] shadow-sm">Today</p>

            <div class="space-y-4">
                @forelse ($messages as $message)
                <x-chat.message-bubble
                    :content="$message->body"
                    :sent="$message->sender_id === auth()->id()"
                    :time="$message->created_at->format('H:i')"
                    :read-at="$message->read_at"
                />
                @empty
                    <p class="py-12 text-center text-sm text-slate-400">No messages yet. Send something sweet to begin.</p>
                @endforelse
            </div>
        </main>

        <x-chat.message-input />
    </section>
</x-chat-layout>
