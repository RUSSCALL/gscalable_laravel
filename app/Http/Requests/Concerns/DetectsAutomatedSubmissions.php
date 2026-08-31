<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

/**
 * Shared bot defence for public, unauthenticated forms.
 *
 * Two signals, both invisible to a human: a honeypot field that only a script
 * fills in, and an encrypted render timestamp that a script submits too fast.
 * Neither is expressed as a validation rule -- a bot must not be told which
 * check it failed, so callers respond with one neutral message instead.
 */
trait DetectsAutomatedSubmissions
{
    /**
     * Minimum seconds between the form rendering and being submitted.
     * Forms with fewer fields can lower this via minimumFillSeconds().
     */
    protected function minimumFillSeconds(): int
    {
        return 3;
    }

    public function looksAutomated(): bool
    {
        // Visually hidden and hidden from assistive tech, so a human never
        // sees it to fill it in.
        if (filled($this->input('website'))) {
            return true;
        }

        $stamp = $this->input('_ts');

        // Missing or unreadable means the form was not rendered by us.
        if (! is_string($stamp) || $stamp === '') {
            return true;
        }

        try {
            $renderedAt = (int) Crypt::decryptString($stamp);
        } catch (DecryptException $e) {
            // Encrypted server-side, so this cannot be forged or back-dated.
            return true;
        }

        return (time() - $renderedAt) < $this->minimumFillSeconds();
    }
}
