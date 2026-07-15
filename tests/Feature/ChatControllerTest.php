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

    private function createConversation(User $firstUser, User $secondUser): Conversation
    {
        return Conversation::query()->create([
            'user_one_id' => min($firstUser->id, $secondUser->id),
            'user_two_id' => max($firstUser->id, $secondUser->id),
        ]);
    }
}
