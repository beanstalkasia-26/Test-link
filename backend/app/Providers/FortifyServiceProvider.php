<?php

namespace App\Providers;

use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Http\Responses\PageResponse;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        Fortify::authenticateUsing(function (Request $request): ?User {
            $user = User::query()->where('email', Str::lower((string) $request->input('email')))->first();

            return $user && $user->is_active && Hash::check((string) $request->input('password'), $user->password) ? $user : null;
        });

        $screen = fn (string $component, array $props = []) => PageResponse::render($component, $props, ['title' => 'Sign in | NAMTECH CMS', 'noindex' => true]);
        Fortify::loginView(fn () => $screen('admin/auth/Login'));
        Fortify::twoFactorChallengeView(fn () => $screen('admin/auth/TwoFactorChallenge'));
        Fortify::requestPasswordResetLinkView(fn () => $screen('admin/auth/ForgotPassword'));
        Fortify::resetPasswordView(fn (Request $request) => $screen('admin/auth/ResetPassword', [
            'token' => $request->route('token'),
            'email' => $request->query('email'),
        ]));
        Fortify::confirmPasswordView(fn () => $screen('admin/auth/ConfirmPassword'));

        ResetPassword::createUrlUsing(fn (User $user, string $token): string => url('/admin/reset-password/'.$token).'?email='.urlencode($user->email));

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return [
                Limit::perMinute(5)->by($throttleKey),
                Limit::perHour(30)->by('login-ip|'.$request->ip()),
            ];
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
