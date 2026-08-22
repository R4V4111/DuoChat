<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\MessagesRead;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Tests\TestCase;

class RealtimeReadReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_messages_read_event_contains_required_frontend_payload(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $conversation = $this->createConversation($user, $partner);
        $now = now();
        $messageIds = [101, 102];

        $event = new MessagesRead($conversation, $messageIds, $now, $user->id);

        $this->assertSame('messages.read', $event->broadcastAs());
        $channels = $event->broadcastOn();
        $this->assertCount(1, $channels);
        $this->assertSame('private-conversation.'.$conversation->id, $channels[0]->name);

        $payload = $event->broadcastWith();
        $this->assertSame($conversation->id, $payload['conversation_id']);
        $this->assertSame($messageIds, $payload['message_ids']);
        $this->assertSame($now->toISOString(), $payload['read_at']);
        $this->assertSame($user->id, $payload['reader_id']);
    }

    public function test_conversation_channel_authorizes_conversation_participants(): void
    {
        $userOne = User::factory()->create();
        $userTwo = User::factory()->create();
        $conversation = $this->createConversation($userOne, $userTwo);

        $channels = Broadcast::driver()->getChannels();
        $callback = $channels['conversation.{conversationId}'] ?? null;

        $this->assertNotNull($callback);
        $this->assertTrue((bool) $callback($userOne, (string) $conversation->id));
        $this->assertTrue((bool) $callback($userTwo, (string) $conversation->id));
    }

    public function test_conversation_channel_rejects_outsiders(): void
    {
        $userOne = User::factory()->create();
        $userTwo = User::factory()->create();
        $conversation = $this->createConversation($userOne, $userTwo);
        $outsider = User::factory()->create();

        $channels = Broadcast::driver()->getChannels();
        $callback = $channels['conversation.{conversationId}'] ?? null;

        $this->assertNotNull($callback);
        $this->assertFalse((bool) $callback($outsider, (string) $conversation->id));
    }

    public function test_authenticated_user_can_mark_messages_as_read_via_post_endpoint(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $conversation = $this->createConversation($user, $partner);

        $unreadMessage = Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $partner->id,
            'body' => 'Unread message from partner',
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('chat.read'));

        $response->assertOk()
            ->assertJson(['marked_read_count' => 1]);

        $this->assertNotNull($unreadMessage->fresh()->read_at);
    }

    public function test_guests_cannot_call_mark_read_endpoint(): void
    {
        $this->post(route('chat.read'))
            ->assertRedirect(route('login'));
    }

    public function test_mark_read_does_not_mark_readers_own_messages_as_read(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $conversation = $this->createConversation($user, $partner);

        $ownMessage = Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => 'My own message',
            'read_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('chat.read'));

        $response->assertOk()
            ->assertJson(['marked_read_count' => 0]);

        $this->assertNull($ownMessage->fresh()->read_at);
    }

    private function createConversation(User $firstUser, User $secondUser): Conversation
    {
        return Conversation::query()->create([
            'user_one_id' => min($firstUser->id, $secondUser->id),
            'user_two_id' => max($firstUser->id, $secondUser->id),
        ]);
    }
}
