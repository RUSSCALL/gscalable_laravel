<?php

namespace App\Http\Middleware;

use App\Support\EnsuresMailIsConfigured;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Guards the "resend verification email" endpoint (Fortify's
 * verification.send route). Without this, a misconfigured mailer causes
 * $user->sendEmailVerificationNotification() to throw and the request
 * to 500, leaving an unverified, permanently-locked-out user with no
 * self-service path.
 */
class EnsureMailIsConfiguredForVerification
{
    use EnsuresMailIsConfigured;

    public function handle(Request $request, Closure $next)
    {
        if ($this->mailIsConfigured()) {
            return $next($request);
        }

        report(new \RuntimeException("Verification email blocked: mail is not configured (mailer: {$this->currentMailer()})."));

        $message = __('We could not send a verification email right now. Please try again later or contact support.');

        if ($request->wantsJson()) {
            return new JsonResponse(['message' => $message], 503);
        }

        return back()->with('status', $message);
    }
}
