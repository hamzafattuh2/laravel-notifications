<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\UserNotification;
use DateTimeInterface;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

class NotificationService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function sendToUser(User $user, array $data): void
    {
        $user->notify($this->buildNotification($data));
    }

    /**
     * @param  array<int, User|int|string>  $users
     * @param  array<string, mixed>  $data
     */
    public function sendToMultiple(array $users, array $data): void
    {
        $resolvedUsers = collect($users)
            ->map(function (User|int|string $user): ?User {
                if ($user instanceof User) {
                    return $user;
                }

                return User::query()->find($user);
            })
            ->filter()
            ->values();

        if ($resolvedUsers->isEmpty()) {
            return;
        }

        Notification::send($resolvedUsers, $this->buildNotification($data));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function buildNotification(array $data): UserNotification
    {
        $title = (string) ($data['title'] ?? '');
        $message = (string) ($data['message'] ?? $data['body'] ?? '');

        if ($title === '' || $message === '') {
            throw new InvalidArgumentException('Notification title and message are required.');
        }

        $type = (string) ($data['type'] ?? 'info');
        $allowedTypes = ['success', 'error', 'info', 'warning'];

        if (! in_array($type, $allowedTypes, true)) {
            $type = 'info';
        }

        $notification = new UserNotification(
            title: $title,
            message: $message,
            type: $type,
            actionUrl: isset($data['action_url']) ? (string) $data['action_url'] : null,
        );

        $notification->afterCommit();

        if (isset($data['delay']) && $data['delay'] !== null) {
            $delay = $this->resolveDelay($data['delay']);

            if ($delay !== null) {
                $notification->delay($delay);
            }
        }

        return $notification;
    }

    private function resolveDelay(mixed $delay): DateTimeInterface|\DateInterval|array|int|null
    {
        if ($delay instanceof DateTimeInterface || $delay instanceof \DateInterval || is_array($delay) || is_int($delay)) {
            return $delay;
        }

        if (is_numeric($delay)) {
            return (int) $delay;
        }

        return null;
    }
}
