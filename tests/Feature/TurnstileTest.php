<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class TurnstileTest extends TestCase
{
    private function attemptLogin(array $extra = [])
    {
        return $this->from(route('login'))->post(route('login.store'), [
            'email' => 'turnstile-'.Str::random(10).'@example.test',
            'password' => 'wrong-password',
        ] + $extra);
    }

    public function test_it_is_skipped_when_no_secret_is_configured(): void
    {
        config(['services.turnstile.secret_key' => null]);
        Http::fake();

        $this->attemptLogin()->assertSessionDoesntHaveErrors('cf-turnstile-response');
        Http::assertNothingSent();
    }

    public function test_a_missing_token_is_rejected(): void
    {
        config(['services.turnstile.secret_key' => 'secret']);
        Http::fake();

        $this->attemptLogin()->assertSessionHasErrors('cf-turnstile-response');
        Http::assertNothingSent();
    }

    public function test_a_token_cloudflare_rejects_is_rejected(): void
    {
        config(['services.turnstile.secret_key' => 'secret']);
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => false])]);

        $this->attemptLogin(['cf-turnstile-response' => 'bad'])
            ->assertSessionHasErrors('cf-turnstile-response');
    }

    public function test_a_valid_token_reaches_fortify(): void
    {
        config(['services.turnstile.secret_key' => 'secret']);
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => true])]);

        $this->attemptLogin(['cf-turnstile-response' => 'good'])
            ->assertSessionDoesntHaveErrors('cf-turnstile-response')
            ->assertSessionHasErrors('email');

        Http::assertSent(fn ($request) => $request['secret'] === 'secret' && $request['response'] === 'good');
    }

    public function test_registration_is_protected(): void
    {
        config(['services.turnstile.secret_key' => 'secret']);
        Http::fake();

        $this->from(route('register'))->post(route('register.store'), [])
            ->assertSessionHasErrors('cf-turnstile-response');
    }

    public function test_the_widget_renders_only_when_a_site_key_is_set(): void
    {
        config(['services.turnstile.site_key' => null]);
        $this->get(route('login'))->assertDontSee('cf-turnstile', false);

        config(['services.turnstile.site_key' => 'site-key-123']);
        $this->get(route('login'))->assertSee('data-sitekey="site-key-123"', false);
        $this->get(route('register'))->assertSee('data-sitekey="site-key-123"', false);
    }
}
