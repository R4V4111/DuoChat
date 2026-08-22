<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\UserPresenceUpdated;
use App\Http\Requests\SendMessageRequest;
use App\Models\User;
use App\Services\ConversationService;
use App\Services\MessageService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
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
    public function store(SendMessageRequest $request): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $conversation = $this->conversationService->findForUser($user);

        abort_if($conversation === null, 404);

        $message = $this->messageService->send($user, $conversation, $request->validated('body'));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => [
                    'id' => $message->id,
                    'conversation_id' => $message->conversation_id,
                    'sender_id' => $message->sender_id,
                    'body' => $message->body,
                    'created_at' => $message->created_at?->toISOString(),
                    'sender' => [
                        'id' => $message->sender->id,
                        'name' => $message->sender->name,
                    ],
                ],
            ]);
        }

        return to_route('chat');
    }

    /**
     * Mark unread messages in the authenticated user's conversation as read.
     */
    public function markRead(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $conversation = $this->conversationService->findForUser($user);

        abort_if($conversation === null, 404);

        $count = $this->messageService->markAsRead($user, $conversation);

        return response()->json([
            'marked_read_count' => $count,
        ]);
    }

    /**
     * Update the authenticated user's online presence and broadcast to partner.
     */
    public function updatePresence(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $conversation = $this->conversationService->findForUser($user);

        abort_if($conversation === null, 404);

        $status = $request->input('status', 'online');
        $isOnline = $status === 'online';

        $now = now();
        $user->update(['last_seen_at' => $now]);

        UserPresenceUpdated::dispatch($conversation, (int) $user->id, $isOnline, $now);

        return response()->json([
            'status' => $status,
            'is_online' => $isOnline,
            'last_seen_at' => $now->toISOString(),
        ]);
    }
}
