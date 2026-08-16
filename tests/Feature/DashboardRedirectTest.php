<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_dashboard_redirects_authenticated_users_to_the_canonical_chat_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('chat'));
    }
}
