<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * API Auth Controller — Mobile (Sanctum token guard).
 *
 * Thin controller that delegates all business logic to AuthService.
 * This ensures web (Livewire) and mobile (API) share identical auth logic.
 */
class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    /**
     * Register a new user and return a Sanctum token.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user  = $this->authService->register($request->validated());
        $token = $this->authService->issueToken($user);

        return response()->json([
            'user'  => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * Login and return a Sanctum token.
     *
     * Rate limited by throttle middleware on the route (5 attempts/minute).
     * Returns a generic error message to avoid email enumeration.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if (! $this->authService->attemptLogin($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial salah.'],
            ]);
        }

        /** @var \App\Models\User $user */
        $user  = Auth::user();
        $token = $this->authService->issueToken($user);

        return response()->json([
            'user'  => new UserResource($user),
            'token' => $token,
        ]);
    }

    /**
     * Logout — revoke the current API token.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logoutApi($request->user());

        return response()->json(['message' => 'Logged out']);
    }

    /**
     * Send a password reset link to the user's email.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = $this->authService->sendResetLink($request->email);

        return response()->json(['message' => __($status)]);
    }

    /**
     * Reset password using a valid reset token.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = $this->authService->resetPassword($request->only(
            'email', 'password', 'password_confirmation', 'token'
        ));

        return response()->json(['message' => __($status)]);
    }

    /**
     * Get the authenticated user's profile.
     */
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
