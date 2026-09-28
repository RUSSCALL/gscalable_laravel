<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class VerifyTurnstile
{
    public function handle(Request $request, Closure $next)
    {
        $secret = config('services.turnstile.secret_key');

        // Unconfigured (local dev, tests): skip rather than lock everyone out.
        if (blank($secret)) {
            return $next($request);
        }

        $token = $request->input('cf-turnstile-response');

        if (blank($token) || ! $this->passes($secret, $token)) {
            throw ValidationException::withMessages([
                'cf-turnstile-response' => __('Please complete the security check and try again.'),
            ]);
        }

        return $next($request);
    }

    private function passes(string $secret, string $token): bool
    {
        try {
            return (bool) Http::asForm()
                ->timeout(5)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secret,
                    'response' => $token,
                ])
                ->json('success', false);
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}
