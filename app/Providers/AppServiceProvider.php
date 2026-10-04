<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            $name = $notifiable->first_name
                ?? $notifiable->name
                ?? 'there';

            return (new MailMessage)
                ->subject('Verify Your WRPlumb Account')
                ->greeting('Hi ' . $name . '!')
                ->line('Welcome to WR Plumbing and Construction Services.')
                ->line('Please verify your email address to activate your client account and access your service records.')
                ->action('Verify Email Address', $url)
                ->line('If you did not create this WRPlumb account, you can safely ignore this email.');
        });
    }
}