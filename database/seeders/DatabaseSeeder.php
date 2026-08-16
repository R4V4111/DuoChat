<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Services\ConversationService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(ConversationService $conversationService): void
    {
        $rasya = User::query()->updateOrCreate(
            ['email' => 'rasya@test.com'],
            [
                'name' => 'Rasya',
                'password' => Hash::make('password123'),
            ],
        );

        $dini = User::query()->updateOrCreate(
            ['email' => 'dini@test.com'],
            [
                'name' => 'Dini',
                'password' => Hash::make('password123'),
            ],
        );

        $conversationService->findOrCreate($rasya, $dini);
    }
}
