<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAlert;

class AlertService
{
    public static function send(?User $user, string $title, string $message, ?string $link = null, string $type = 'info'): void
    {
        if (!$user) {
            return;
        }

        UserAlert::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'link' => $link,
            'is_read' => false,
        ]);
    }

    public static function sendToUsers(iterable $users, string $title, string $message, ?string $link = null, string $type = 'info'): void
    {
        foreach ($users as $user) {
            self::send($user, $title, $message, $link, $type);
        }
    }

    public static function sendToRole(string $role, string $title, string $message, ?string $link = null, string $type = 'info'): void
    {
        $users = User::where('role', $role)->where('is_active', true)->get();

        self::sendToUsers($users, $title, $message, $link, $type);
    }
}