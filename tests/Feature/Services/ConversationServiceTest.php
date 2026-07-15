<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\Conversation;
use App\Models\User;
use App\Services\ConversationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ConversationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_finds_an_existing_conversation_regardless_of_user_order(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $conversation = Conversation::query()->create([
            'user_one_id' => min($firstUser->id, $secondUser->id),
            'user_two_id' => max($firstUser->id, $secondUser->id),
        ]);

        $foundConversation = app(ConversationService::class)
            ->findBetweenUsers($secondUser, $firstUser);

        $this->assertTrue($conversation->is($foundConversation));
    }

    public function test_it_creates_a_conversation_with_normalized_user_ids(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        $conversation = app(ConversationService::class)
            ->findOrCreate($secondUser, $firstUser);

        $this->assertSame(min($firstUser->id, $secondUser->id), $conversation->user_one_id);
        $this->assertSame(max($firstUser->id, $secondUser->id), $conversation->user_two_id);
        $this->assertDatabaseCount('conversations', 1);
    }

    public function test_it_reuses_an_existing_conversation_instead_of_creating_a_duplicate(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $service = app(ConversationService::class);

        $firstConversation = $service->findOrCreate($firstUser, $secondUser);
        $secondConversation = $service->findOrCreate($secondUser, $firstUser);

        $this->assertTrue($firstConversation->is($secondConversation));
        $this->assertDatabaseCount('conversations', 1);
    }

    public function test_it_rejects_a_conversation_with_the_same_user(): void
    {
        $user = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        app(ConversationService::class)->findOrCreate($user, $user);
    }
}
