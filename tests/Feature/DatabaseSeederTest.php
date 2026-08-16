<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_exactly_two_users_and_one_conversation_idempotently(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $rasya = User::query()->where('email', 'rasya@test.com')->firstOrFail();
        $dini = User::query()->where('email', 'dini@test.com')->firstOrFail();

        $this->assertSame('Rasya', $rasya->name);
        $this->assertSame('Dini', $dini->name);
        $this->assertTrue(Hash::check('password123', $rasya->password));
        $this->assertTrue(Hash::check('password123', $dini->password));
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('conversations', 1);
        $this->assertDatabaseHas('conversations', [
            'user_one_id' => min($rasya->id, $dini->id),
            'user_two_id' => max($rasya->id, $dini->id),
        ]);
    }
}
