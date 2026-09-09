<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['nullable', 'string'],
            'username' => ['nullable', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): User
    {
        $this->ensureIsNotRateLimited();

        $loginInput = trim((string) ($this->input('email') ?? $this->input('username') ?? ''));
        $password = (string) $this->input('password', '');
        $remember = $this->boolean('remember');

        if ($loginInput === '') {
            throw ValidationException::withMessages([
                'email' => 'Please provide your email address or username.',
            ]);
        }

        // Determine whether input is an email address or username/name
        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'email';

        // Check if user exists by email or name/username
        $user = User::where('email', $loginInput)
            ->orWhere('name', $loginInput)
            ->first();

        if (! $user || ! Auth::attempt(['email' => $user->email, 'password' => $password], $remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        if (! $user->is_active) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Your account has been deactivated. Please contact your Super Administrator.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        // Update last login timestamp
        $user->forceFill(['last_login_at' => now()])->save();

        return $user;
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        $loginInput = trim((string) ($this->input('email') ?? $this->input('username') ?? ''));

        return Str::transliterate(Str::lower($loginInput).'|'.$this->ip());
    }
}
