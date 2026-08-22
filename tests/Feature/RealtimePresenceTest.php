<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\MessagesRead;
use App\Events\UserPresenceUpdated;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RealtimePresenceTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Event payload tests
    // -----------------------------------------------------------------------

    public function test_user_presence_updated_event_has_correct_payload(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $conversation = $this->createConversation($user, $partner);
        $now = now();

        $event = new UserPresenceUpdated($conversation, $user->id, true, $now);

        $this->assertSame('user.presence', $event->broadcastAs());

        $channels = $event->broadcastOn();
        $this->assertCount(1, $channels);
        $this->assertSame('private-conversation.'.$conversation->id, $channels[0]->name);

        $payload = $event->broadcastWith();
        $this->assertSame($conversation->id, $payload['conversation_id']);
        $this->assertSame($user->id, $payload['user_id']);
        $this->assertTrue($payload['is_online']);
        $this->assertSame($now->toISOString(), $payload['last_seen_at']);
        $this->assertStringContainsString('Last seen', $payload['last_seen_diff']);
    }

    public function test_user_presence_updated_event_offline_payload(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $conversation = $this->createConversation($user, $partner);
        $lastSeen = now()->subMinutes(5);

        $event = new UserPresenceUpdated($conversation, $user->id, false, $lastSeen);

        $payload = $event->broadcastWith();
        $this->assertFalse($payload['is_online']);
        $this->assertSame($lastSeen->toISOString(), $payload['last_seen_at']);
    }

    public function test_user_presence_updated_event_with_null_last_seen(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $conversation = $this->createConversation($user, $partner);

        $event = new UserPresenceUpdated($conversation, $user->id, false, null);

        $payload = $event->broadcastWith();
        $this->assertNull($payload['last_seen_at']);
        $this->assertSame('Never seen', $payload['last_seen_diff']);
    }

    // -----------------------------------------------------------------------
    // HTTP endpoint tests
    // -----------------------------------------------------------------------

    public function test_authenticated_user_can_announce_online_presence(): void
    {
        Event::fake([UserPresenceUpdated::class]);

        $user = User::factory()->create();
        $partner = User::factory()->create();
        $this->createConversation($user, $partner);

        $response = $this->actingAs($user)
            ->postJson(route('chat.presence'), ['status' => 'online']);

        $response->assertOk()
            ->assertJsonFragment(['status' => 'online', 'is_online' => true]);

        Event::assertDispatched(UserPresenceUpdated::class, function (UserPresenceUpdated $event) use ($user): bool {
            return $event->userId === $user->id && $event->isOnline === true;
        });
    }

    public function test_authenticated_user_can_announce_offline_presence(): void
    {
        Event::fake([UserPresenceUpdated::class]);

        $user = User::factory()->create();
        $partner = User::factory()->create();
        $this->createConversation($user, $partner);

        $response = $this->actingAs($user)
            ->postJson(route('chat.presence'), ['status' => 'offline']);

        $response->assertOk()
            ->assertJsonFragment(['status' => 'offline', 'is_online' => false]);

        Event::assertDispatched(UserPresenceUpdated::class, function (UserPresenceUpdated $event) use ($user): bool {
            return $event->userId === $user->id && $event->isOnline === false;
        });
    }

    public function test_presence_updates_last_seen_at_in_database(): void
    {
        Event::fake([UserPresenceUpdated::class]);

        $user = User::factory()->create(['last_seen_at' => null]);
        $partner = User::factory()->create();
        $this->createConversation($user, $partner);

        $this->actingAs($user)
            ->postJson(route('chat.presence'), ['status' => 'online'])
            ->assertOk();

        $user->refresh();
        $this->assertNotNull($user->last_seen_at);
    }

    public function test_guests_cannot_call_presence_endpoint(): void
    {
        $this->post(route('chat.presence'))
            ->assertRedirect(route('login'));
    }

    // -----------------------------------------------------------------------
    // Non-interference regression tests
    // -----------------------------------------------------------------------

    public function test_presence_update_does_not_mark_messages_as_read(): void
    {
        Event::fake([UserPresenceUpdated::class]);

        $user = User::factory()->create();
        $partner = User::factory()->create();
        $conversation = $this->createConversation($user, $partner);

        $unreadMessage = Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $partner->id,
            'body' => 'Unread from partner',
            'read_at' => null,
        ]);

        $this->actingAs($user)
            ->postJson(route('chat.presence'), ['status' => 'online'])
            ->assertOk();

        $this->assertNull($unreadMessage->fresh()->read_at);
    }

    public function test_existing_messages_read_event_is_not_dispatched_by_presence_update(): void
    {
        Event::fake([MessagesRead::class, UserPresenceUpdated::class]);

        $user = User::factory()->create();
        $partner = User::factory()->create();
        $conversation = $this->createConversation($user, $partner);

        Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $partner->id,
            'body' => 'Unread',
            'read_at' => null,
        ]);

        $this->actingAs($user)
            ->postJson(route('chat.presence'), ['status' => 'online'])
            ->assertOk();

        Event::assertNotDispatched(MessagesRead::class);
        Event::assertDispatched(UserPresenceUpdated::class);
    }

    public function test_message_sending_still_works_after_presence_is_added(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $this->createConversation($user, $partner);

        $response = $this->actingAs($user)
            ->postJson(route('chat.send'), ['body' => 'Hello from presence test']);

        $response->assertOk()
            ->assertJsonPath('message.body', 'Hello from presence test');
    }

    public function test_read_receipt_still_works_after_presence_is_added(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $conversation = $this->createConversation($user, $partner);

        $unreadMessage = Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $partner->id,
            'body' => 'Message to read',
            'read_at' => null,
        ]);

        $this->actingAs($user)
            ->postJson(route('chat.read'))
            ->assertOk()
            ->assertJson(['marked_read_count' => 1]);

        $this->assertNotNull($unreadMessage->fresh()->read_at);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function createConversation(User $firstUser, User $secondUser): Conversation
    {
        return Conversation::query()->create([
            'user_one_id' => min($firstUser->id, $secondUser->id),
            'user_two_id' => max($firstUser->id, $secondUser->id),
        ]);
    }
}
