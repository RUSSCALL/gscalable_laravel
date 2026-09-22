<?php

namespace App\Support;

/**
 * Shared guard used anywhere the app is about to trigger an outbound
 * verification/notification email (registration, resend verification, …).
 *
 * Centralized so every mail-sending entry point agrees on what "mail is
 * configured" means, instead of each caller reimplementing the check.
 */
trait EnsuresMailIsConfigured
{
    /**
     * Determine whether the active mailer has the credentials it needs to
     * actually send. Mailers like 'log' or 'array' need none and always pass.
     */
    protected function mailIsConfigured(): bool
    {
        $mailer = config('mail.default');
        $config = config("mail.mailers.{$mailer}", []);

        if (! in_array($mailer, ['smtp', 'ses', 'mailgun', 'postmark'], true)) {
            return true;
        }

        return match ($mailer) {
            'smtp' => ! empty($config['host']) && ! empty($config['username']) && ! empty($config['password']),
            default => ! empty(config('services.'.$mailer.'.secret'))
                || ! empty(config('services.'.$mailer.'.key'))
                || ! empty(config('services.'.$mailer.'.token')),
        };
    }

    /**
     * The mailer name, used for logging which mailer was misconfigured.
     */
    protected function currentMailer(): string
    {
        return config('mail.default');
    }
}
