<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ConversationService
{
    /**
     * Find the conversation that includes a user.
     */
    public function findForUser(User $user): ?Conversation
    {
        return Conversation::query()
            ->where(function (Builder $query) use ($user): void {
                $query->where('user_one_id', $user->getKey())
                    ->orWhere('user_two_id', $user->getKey());
            })
            ->with(['userOne', 'userTwo'])
            ->first();
    }

    /**
     * Find the conversation shared by two users.
     */
    public function findBetweenUsers(User $a, User $b): ?Conversation
    {
        [$userOneId, $userTwoId] = $this->normalizedUserIds($a, $b);

        return Conversation::query()
            ->where('user_one_id', $userOneId)
            ->where('user_two_id', $userTwoId)
            ->first();
    }

    /**
     * Find the conversation shared by two users, or create it if it does not exist.
     */
    public function findOrCreate(User $a, User $b): Conversation
    {
        [$userOneId, $userTwoId] = $this->normalizedUserIds($a, $b);

        try {
            return DB::transaction(function () use ($userOneId, $userTwoId): Conversation {
                $conversation = Conversation::query()
                    ->where('user_one_id', $userOneId)
                    ->where('user_two_id', $userTwoId)
                    ->lockForUpdate()
                    ->first();

                if ($conversation !== null) {
                    return $conversation;
                }

                return Conversation::query()->create([
                    'user_one_id' => $userOneId,
                    'user_two_id' => $userTwoId,
                ]);
            });
        } catch (QueryException $exception) {
            $conversation = $this->findBetweenUsers($a, $b);

            if ($conversation !== null) {
                return $conversation;
            }

            throw $exception;
        }
    }

    /**
     * Get two persisted user IDs in the order used by the conversations table.
     *
     * @return array{0: int, 1: int}
     */
    private function normalizedUserIds(User $a, User $b): array
    {
        $firstUserId = $a->getKey();
        $secondUserId = $b->getKey();

        if ($firstUserId === null || $secondUserId === null) {
            throw new InvalidArgumentException('Conversations require persisted users.');
        }

        $firstUserId = (int) $firstUserId;
        $secondUserId = (int) $secondUserId;

        if ($firstUserId === $secondUserId) {
            throw new InvalidArgumentException('A conversation requires two different users.');
        }

        return $firstUserId < $secondUserId
            ? [$firstUserId, $secondUserId]
            : [$secondUserId, $firstUserId];
    }
}
