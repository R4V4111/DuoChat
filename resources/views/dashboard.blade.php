<x-chat-layout>
    <x-slot:sidebar>
        <x-chat.sidebar />
    </x-slot:sidebar>

    <section class="flex min-h-screen min-w-0 flex-1 flex-col bg-stone-100">
        <x-chat.header />

        <main class="flex-1 space-y-6 overflow-y-auto px-4 py-6 sm:px-6 lg:px-8" aria-label="Chat messages">
            <p class="text-center text-xs font-medium uppercase tracking-[0.16em] text-slate-400">Today</p>

            <x-chat.message-bubble content="Hi! How is your day going?" time="10:22" />
            <x-chat.message-bubble content="Really well, thank you. I am looking forward to catching up later." time="10:23" sent />
            <x-chat.message-bubble content="Same here. See you this afternoon." time="10:24" />
        </main>

        <x-chat.message-input />
    </section>
</x-chat-layout>
