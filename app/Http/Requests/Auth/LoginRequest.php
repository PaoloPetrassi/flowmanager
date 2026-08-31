<?php

namespace App\Http\Requests\Auth;

use App\Models\LoginActivity;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            $email = strtolower(trim((string) $this->input('email')));
            $user = User::query()->where('email', $email)->first();

            LoginActivity::create([
                'user_id' => $user?->id,
                'email' => $email,
                'event' => 'login_failed',
                'ip_address' => $this->ip(),
                'user_agent' => mb_substr((string) $this->userAgent(), 0, 1000),
            ]);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        LoginActivity::create([
            'user_id' => User::query()->where('email', strtolower(trim((string) $this->input('email'))))->value('id'),
            'email' => strtolower(trim((string) $this->input('email'))),
            'event' => 'locked_out',
            'ip_address' => $this->ip(),
            'user_agent' => mb_substr((string) $this->userAgent(), 0, 1000),
        ]);

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower((string) $this->input('email')).'|'.$this->ip()
        );
    }
}
