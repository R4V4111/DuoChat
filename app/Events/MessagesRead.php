<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Conversation;
use Carbon\CarbonInterface;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessagesRead implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    /**
     * @param  array<int>  $messageIds
     */
    public function __construct(
        public readonly Conversation $conversation,
        public readonly array $messageIds,
        public readonly CarbonInterface $readAt,
        public readonly int $readerId,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.'.$this->conversation->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'messages.read';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversation->id,
            'message_ids' => $this->messageIds,
            'read_at' => $this->readAt->toISOString(),
            'reader_id' => $this->readerId,
        ];
    }
}
