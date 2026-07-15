<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\Conversation;
use App\Models\User;
use App\Services\MessageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class MessageServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_trims_and_saves_a_message_from_a_conversation_participant(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $conversation = $this->createConversation($sender, $recipient);

        $message = app(MessageService::class)->send($sender, $conversation, "  Hello DuoChat  \n");

        $this->assertSame('Hello DuoChat', $message->body);
        $this->assertSame($conversation->id, $message->conversation_id);
        $this->assertSame($sender->id, $message->sender_id);
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'body' => 'Hello DuoChat',
        ]);
    }

    public function test_it_rejects_an_empty_message_after_trimming_whitespace(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $conversation = $this->createConversation($sender, $recipient);

        $this->expectException(InvalidArgumentException::class);

        app(MessageService::class)->send($sender, $conversation, " \n\t ");
    }

    public function test_it_rejects_a_message_longer_than_five_thousand_characters(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $conversation = $this->createConversation($sender, $recipient);

        $this->expectException(InvalidArgumentException::class);

        app(MessageService::class)->send($sender, $conversation, str_repeat('a', 5001));
    }

    public function test_it_rejects_a_sender_who_does_not_belong_to_the_conversation(): void
    {
        $firstParticipant = User::factory()->create();
        $secondParticipant = User::factory()->create();
        $outsider = User::factory()->create();
        $conversation = $this->createConversation($firstParticipant, $secondParticipant);

        $this->expectException(InvalidArgumentException::class);

        app(MessageService::class)->send($outsider, $conversation, 'Hello');
    }

    private function createConversation(User $firstUser, User $secondUser): Conversation
    {
        return Conversation::query()->create([
            'user_one_id' => min($firstUser->id, $secondUser->id),
            'user_two_id' => max($firstUser->id, $secondUser->id),
        ]);
    }
}
