<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /*****************************************************************************************/
    /* Processing Hierarchy                                                                  */
    /*****************************************************************************************/
    // authorize                (1.0) Determine if the user is authorized to make this request.
    // rules                    (2.0) Get the validation rules that apply to the request.
    // authenticate             (3.0) Attempt to authenticate the request's credentials.
    // ensureIsNotRateLimited   (4.0) Ensure the login request is not rate limited.
    // throttleKey              (5.0) Get the rate limiting throttle key for the request.

    /**
     * * <Layer number> (1.0)
     *
     * <Processing name> authorize
     * <Function> Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * * * <Layer number> (2.0)
     *
     * <Processing name> rules
     * <Function> Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * * * <Layer number> (3.0)
     *
     * <Processing name> authenticate
     * <Function> Attempt to authenticate the request's credentials.
     *
     */
    public function authenticate(): void
    {

        $this->ensureIsNotRateLimited();

        if (!auth('web')->attempt($this->safe()->only('username', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'status' => trans('auth.failed')
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * * * <Layer number> (4.0)
     *
     * <Processing name> ensureIsNotRateLimited
     * <Function> Ensure the login request is not rate limited.
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * * * <Layer number> (5.0)
     *
     * <Processing name> throttleKey
     * <Function> Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('username')) . '|' . $this->ip());
    }
}
