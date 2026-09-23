<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });



        Fortify::loginView(function(){
            return view('auth.login');
        });

        Fortify::registerView(function(){
            return view('auth.register');
        });

        Fortify::requestPasswordResetLinkView(function(){
            return view('auth.forgot-password');
        });

        Fortify::resetPasswordView(function($request){
            return view('auth.reset-password' , ['request' => $request]);
        });

        Fortify::verifyEmailView(function(){

            return view ('auth.verify-email');
        });

        // Guard the "resend verification email" endpoint: if mail isn't
        // configured, fail gracefully instead of 500ing when
        // $user->sendEmailVerificationNotification() throws.
        //
        // Fortify's route file has no `register` entry in its `limiters`
        // config (unlike `login`/`two-factor`), so throttling it has to be
        // bolted on here, the same way, after Fortify registers the route.
        $this->app->booted(function () {
            optional(Route::getRoutes()->getByName('verification.send'))
                ->middleware('mail.configured');

            optional(Route::getRoutes()->getByName('register.store'))
                ->middleware('throttle:6,1');
        });
    }
}
