<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnlineStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_table_has_last_seen_at_column(): void
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'last_seen_at' => null,
        ]);
    }

    public function test_middleware_updates_last_seen_at_on_authenticated_request(): void
    {
        $user = User::factory()->create(['last_seen_at' => null]);

        $this->actingAs($user)->get(route('chat'));

        $user->refresh();
        $this->assertNotNull($user->last_seen_at);
        $this->assertTrue($user->last_seen_at->isCurrentMinute());
    }

    public function test_middleware_does_not_update_last_seen_for_guests(): void
    {
        $this->get(route('chat'))->assertRedirect(route('login'));

        // No exception, just verify no error
    }

    public function test_is_online_returns_true_when_last_seen_within_three_minutes(): void
    {
        $user = User::factory()->create(['last_seen_at' => now()->subMinutes(2)]);

        $this->assertTrue($user->isOnline());
    }

    public function test_is_online_returns_true_when_last_seen_exactly_three_minutes_ago(): void
    {
        Carbon::setTestNow('2026-08-19 12:00:00');
        $user = User::factory()->create(['last_seen_at' => '2026-08-19 11:57:00']); // exactly 3 minutes ago
        $this->assertTrue($user->isOnline());
        Carbon::setTestNow();
    }

    public function test_is_online_returns_false_when_last_seen_exceeds_three_minutes(): void
    {
        $user = User::factory()->create(['last_seen_at' => now()->subMinutes(4)]);

        $this->assertFalse($user->isOnline());
    }

    public function test_is_online_returns_false_when_last_seen_is_null(): void
    {
        $user = User::factory()->create(['last_seen_at' => null]);

        $this->assertFalse($user->isOnline());
    }

    public function test_is_online_uses_carbon_test_now_for_deterministic_testing(): void
    {
        Carbon::setTestNow('2026-08-19 12:00:00');

        $user = User::factory()->create(['last_seen_at' => '2026-08-19 11:58:00']); // 2 minutes ago
        $this->assertTrue($user->isOnline());

        Carbon::setTestNow('2026-08-19 12:05:00'); // 7 minutes later
        $user->refresh(); // reload to get fresh attribute
        $this->assertFalse($user->isOnline());

        Carbon::setTestNow(); // reset
    }
}
