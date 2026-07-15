<x-chat-layout>
    <x-slot:sidebar>
        <x-chat.sidebar :partner="$partner" />
    </x-slot:sidebar>

    <section class="flex min-h-screen min-w-0 flex-1 flex-col bg-stone-100">
        <x-chat.header :partner="$partner" />

        <main class="flex-1 space-y-6 overflow-y-auto px-4 py-6 sm:px-6 lg:px-8" aria-label="Chat messages">
            @forelse ($messages as $message)
                <x-chat.message-bubble
                    :content="$message->body"
                    :sent="$message->sender_id === auth()->id()"
                    :time="$message->created_at->format('H:i')"
                />
            @empty
                <p class="py-8 text-center text-sm text-slate-400">No messages yet. Start the conversation when messaging is available.</p>
            @endforelse
        </main>

        <x-chat.message-input />
    </section>
</x-chat-layout>
