<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

/**
 * AuthService — Single source of truth for authentication business logic.
 *
 * Used by both Livewire components (web, session guard) and
 * API controllers (mobile, Sanctum token guard).
 * No auth logic should be duplicated outside this service.
 */
class AuthService
{
    /**
     * Register a new user.
     *
     * Note: Password hashing is handled by the User model's 'hashed' cast,
     * so we pass the plain password directly to create().
     */
    public function register(array $data): User
    {
        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'], // hashed by model cast
        ]);
    }

    /**
     * Attempt login with email and password via the web (session) guard.
     *
     * @param  array{email: string, password: string}  $credentials
     * @param  bool  $remember  Whether to set "remember me" cookie
     * @return bool  True if authentication succeeded
     */
    public function attemptLogin(array $credentials, bool $remember = false): bool
    {
        return Auth::attempt($credentials, $remember);
    }

    /**
     * Issue a Sanctum personal access token for API usage.
     *
     * @return string  Plain-text token (shown only once)
     */
    public function issueToken(User $user, string $tokenName = 'auth_token'): string
    {
        return $user->createToken($tokenName)->plainTextToken;
    }

    /**
     * Revoke the current API token (logout for mobile).
     */
    public function logoutApi(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * Send a password reset link to the given email address.
     *
     * @return string  One of the Password::RESET_LINK_* constants
     */
    public function sendResetLink(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    /**
     * Reset the user's password using a valid token.
     *
     * @param  array{email: string, password: string, password_confirmation: string, token: string}  $data
     * @return string  One of the Password::PASSWORD_* constants
     */
    public function resetPassword(array $data): string
    {
        return Password::reset($data, function (User $user, string $password) {
            $user->forceFill(['password' => $password])->save(); // hashed by model cast
        });
    }
}
