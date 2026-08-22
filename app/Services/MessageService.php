<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\MessageSent;
use App\Events\MessagesRead;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MessageService
{
    /**
     * Send a message in a conversation on behalf of one of its participants.
     */
    public function send(User $sender, Conversation $conversation, string $body): Message
    {
        $body = $this->validatedBody($body);
        $this->ensureSenderBelongsToConversation($sender, $conversation);

        $message = $conversation->messages()->create([
            'sender_id' => $sender->getKey(),
            'body' => $body,
        ]);

        // Dispatch the realtime event after the message is created
        MessageSent::dispatch($message->load('sender'), $conversation);

        return $message;
    }

    /**
     * Mark messages from the partner as read for the given user.
     */
    public function markAsRead(User $user, Conversation $conversation): int
    {
        $this->ensureSenderBelongsToConversation($user, $conversation);

        $unreadMessages = $conversation->messages()
            ->where('sender_id', '!=', $user->getKey())
            ->whereNull('read_at')
            ->get();

        if ($unreadMessages->isEmpty()) {
            return 0;
        }

        $now = now();
        $messageIds = $unreadMessages->pluck('id')->map(fn ($id) => (int) $id)->all();

        $conversation->messages()
            ->whereIn('id', $messageIds)
            ->update(['read_at' => $now]);

        MessagesRead::dispatch($conversation, $messageIds, $now, (int) $user->getKey());

        return count($messageIds);
    }

    /**
     * Trim and validate a message body.
     */
    private function validatedBody(string $body): string
    {
        $body = trim($body);

        if ($body === '') {
            throw new InvalidArgumentException('A message body cannot be empty.');
        }

        if (Str::length($body) > 5000) {
            throw new InvalidArgumentException('A message body cannot exceed 5000 characters.');
        }

        return $body;
    }

    /**
     * Ensure the sender is one of the conversation's two participants.
     */
    private function ensureSenderBelongsToConversation(User $sender, Conversation $conversation): void
    {
        $senderId = (int) $sender->getKey();

        if (
            $senderId !== (int) $conversation->user_one_id
            && $senderId !== (int) $conversation->user_two_id
        ) {
            throw new InvalidArgumentException('The sender does not belong to this conversation.');
        }
    }
}
