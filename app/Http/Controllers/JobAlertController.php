<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobAlertRequest;
use App\Mail\JobAlertConfirmation;
use App\Models\JobAlertSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Job-alert subscriptions: sign-up, double opt-in confirmation, unsubscribe.
 *
 * Nothing here reveals whether a given address is already subscribed -- the
 * form is public, so a response that differed between "new" and "existing"
 * would turn it into an address checker.
 */
class JobAlertController extends Controller
{
    public function subscribe(StoreJobAlertRequest $request): RedirectResponse
    {
        // Answer a bot exactly as we answer a human, so it learns nothing.
        if ($request->looksAutomated()) {
            return $this->done();
        }

        $data = $request->subscriptionData();

        $subscription = JobAlertSubscription::where('email', $data['email'])
            ->where('category_id', $data['category_id'])
            ->where('location_id', $data['location_id'])
            ->first();

        if ($subscription && $subscription->is_confirmed) {
            // Already live. Quietly keep the keywords current and send nothing:
            // re-sending a confirmation here would let anyone mail-bomb an
            // address by resubmitting the form.
            $subscription->update(['keywords' => $data['keywords']]);

            return $this->done();
        }

        if ($subscription) {
            // Pending from an earlier attempt -- refresh it and re-send, so a
            // lost confirmation email is recoverable.
            $subscription->update($data);
        } else {
            $subscription = JobAlertSubscription::create($data);
        }

        $this->sendConfirmation($subscription);

        return $this->done();
    }

    public function confirm(string $token): RedirectResponse
    {
        $subscription = JobAlertSubscription::where('confirmation_token', $token)->first();

        if (! $subscription) {
            // Either a bad link, or a good one already used -- the token is
            // nulled on confirmation. Both are safe to treat as "you're set",
            // because reaching this URL at all required the emailed token.
            return redirect()->route('careers')
                ->with('alert_status', 'This link has already been used. Your alerts are active.');
        }

        $subscription->confirm();

        return redirect()->route('careers')
            ->with('alert_success', "You're subscribed. We'll email you when a matching role opens.");
    }

    public function unsubscribe(string $token): RedirectResponse
    {
        $subscription = JobAlertSubscription::where('unsubscribe_token', $token)->first();

        // Unsubscribing must always look like it worked, even on a stale link,
        // so nobody is left thinking they are still on the list.
        $subscription?->delete();

        return redirect()->route('careers')
            ->with('alert_status', "You've been unsubscribed. We won't email you about new roles.");
    }

    /**
     * One response for every sign-up outcome: new, pending, already confirmed,
     * or bot.
     */
    private function done(): RedirectResponse
    {
        return redirect()->route('careers')
            ->with('alert_success', 'Check your inbox — click the link in the email to confirm your alerts.')
            ->withFragment('job-alerts');
    }

    private function sendConfirmation(JobAlertSubscription $subscription): void
    {
        try {
            Mail::to($subscription->email)->send(new JobAlertConfirmation($subscription));
        } catch (\Throwable $e) {
            // A mail outage must not lose the subscription or show a stack
            // trace on a public page. The row survives and can be re-sent.
            Log::error('Job alert confirmation failed to send', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
