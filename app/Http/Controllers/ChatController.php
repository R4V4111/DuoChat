<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SendMessageRequest;
use App\Models\User;
use App\Services\ConversationService;
use App\Services\MessageService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        private readonly ConversationService $conversationService,
        private readonly MessageService $messageService,
    ) {}

    /**
     * Display the authenticated user's private conversation.
     */
    public function show(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $conversation = $this->conversationService->findForUser($user);

        abort_if($conversation === null, 404);

        $this->messageService->markAsRead($user, $conversation);

        $partner = $conversation->user_one_id === $user->id
            ? $conversation->userTwo
            : $conversation->userOne;

        $messages = $conversation->messages()
            ->with('sender')
            ->oldest()
            ->get();

        return view('chat.show', compact('conversation', 'partner', 'messages'));
    }

    /**
     * Store a message in the authenticated user's conversation.
     */
    public function store(SendMessageRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $conversation = $this->conversationService->findForUser($user);

        abort_if($conversation === null, 404);

        $this->messageService->send($user, $conversation, $request->validated('body'));

        return to_route('chat');
    }
}
