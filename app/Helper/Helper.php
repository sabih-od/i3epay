<?php

namespace App\Helper;

use Illuminate\Http\JsonResponse;
use Notification;
use App\Notifications\NewUserNotification;
use App\Models\User;

class Helper
{
    public function sendUserNotification(User $user, String $message) {
        Notification::send($user, new NewUserNotification($message));
    }

    public function name() {
        return auth()->user()->firstname. ' ' .auth()->user()->lastname;
    }
}