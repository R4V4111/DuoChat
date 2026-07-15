<?php

declare(strict_types=1);

namespace App\Services;

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

        return $conversation->messages()->create([
            'sender_id' => $sender->getKey(),
            'body' => $body,
        ]);
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
