<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_view_the_chat(): void
    {
        $this->get(route('chat'))
            ->assertRedirect(route('login'));
    }

    public function test_it_displays_the_authenticated_users_conversation_with_messages_and_senders(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();
        $conversation = $this->createConversation($user, $partner);
        $olderMessage = Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $partner->id,
            'body' => 'Earlier message',
            'created_at' => now()->subMinute(),
            'updated_at' => now()->subMinute(),
        ]);
        $newerMessage = Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => 'Later message',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('chat'));

        $response->assertOk()
            ->assertViewIs('chat.show')
            ->assertSee('sm:!block')
            ->assertSee('sm:!flex')
            ->assertSee('sm:!h-screen')
            ->assertViewHas('conversation', fn (Conversation $viewConversation): bool => $viewConversation->is($conversation))
            ->assertViewHas('partner', fn (User $viewPartner): bool => $viewPartner->is($partner))
            ->assertViewHas('messages', function ($messages) use ($olderMessage, $newerMessage): bool {
                return $messages->pluck('id')->all() === [$olderMessage->id, $newerMessage->id]
                    && $messages->every(fn (Message $message): bool => $message->relationLoaded('sender'));
            });
    }

    public function test_it_returns_not_found_when_the_authenticated_user_has_no_conversation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('chat'))
            ->assertNotFound();
    }

    public function test_an_authenticated_user_can_send_a_message(): void
    {
        $sender = User::factory()->create();
        $partner = User::factory()->create();
        $this->createConversation($sender, $partner);

        $this->actingAs($sender)
            ->post(route('chat.send'), ['body' => 'Hello'])
            ->assertRedirect(route('chat'));
    }

    public function test_a_sent_message_is_stored_in_the_database(): void
    {
        $sender = User::factory()->create();
        $partner = User::factory()->create();
        $conversation = $this->createConversation($sender, $partner);

        $this->actingAs($sender)
            ->post(route('chat.send'), ['body' => '  Hello DuoChat  ']);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'body' => 'Hello DuoChat',
        ]);
    }

    public function test_an_empty_message_is_rejected(): void
    {
        $sender = User::factory()->create();
        $partner = User::factory()->create();
        $this->createConversation($sender, $partner);

        $this->from(route('chat'))
            ->actingAs($sender)
            ->post(route('chat.send'), ['body' => ''])
            ->assertRedirect(route('chat'))
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('messages', 0);
    }

    public function test_a_user_cannot_send_a_message_to_another_users_conversation(): void
    {
        $sender = User::factory()->create();
        $partner = User::factory()->create();
        $sendersConversation = $this->createConversation($sender, $partner);
        $otherUser = User::factory()->create();
        $otherPartner = User::factory()->create();
        $otherConversation = $this->createConversation($otherUser, $otherPartner);

        $this->actingAs($sender)
            ->post(route('chat.send'), [
                'body' => 'This must stay in my conversation.',
                'conversation_id' => $otherConversation->id,
            ])
            ->assertRedirect(route('chat'));

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $sendersConversation->id,
            'sender_id' => $sender->id,
        ]);
        $this->assertDatabaseMissing('messages', [
            'conversation_id' => $otherConversation->id,
            'sender_id' => $sender->id,
        ]);
    }

    private function createConversation(User $firstUser, User $secondUser): Conversation
    {
        return Conversation::query()->create([
            'user_one_id' => min($firstUser->id, $secondUser->id),
            'user_two_id' => max($firstUser->id, $secondUser->id),
        ]);
    }
}
