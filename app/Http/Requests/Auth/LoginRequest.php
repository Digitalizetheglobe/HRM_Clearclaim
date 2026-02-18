<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Models\Utility;
use App\Models\IpRestrict;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    // public function authenticate()
    // {
    //     $this->ensureIsNotRateLimited();

    //     if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
    //         RateLimiter::hit($this->throttleKey());

    //         throw ValidationException::withMessages([
    //             'email' => trans('auth.failed'),
    //         ]);
    //     }

    //     RateLimiter::clear($this->throttleKey());
    // }

    public function authenticate()
    {
        // custom login
        $users = User::where('email',$this->email)->get();
        $id = 0;
        if(count($users) > 0)
        {
            foreach ($users as $key => $user) {
                if(password_verify($this->password,$user->password))
                {
                    if($user->is_active != 1 || $user->is_disable != 1 && $user->type != "super admin")
                    {
                        throw ValidationException::withMessages([
                            'email' => __("Your Account is disable, please contact your Administrate."),
                        ]);
                    }elseif ($user->is_login_enable != 1) {
                        throw ValidationException::withMessages([
                            'email' => __("Your account is disabled from company."),
                        ]);
                    }

                    // IP Restriction Check for login
                    $settings = Utility::settings();
                    if (!empty($settings['ip_restrict']) && $settings['ip_restrict'] == 'on') {
                        $userIp = $this->getRealClientIp();
                        $ipRestrictions = IpRestrict::where('created_by', $user->creatorId())->get();
                        
                        $isAllowed = false;
                        foreach ($ipRestrictions as $ipRestriction) {
                            if ($ipRestriction->matchesIp($userIp)) {
                                $isAllowed = true;
                                break;
                            }
                        }
                        
                        if (!$isAllowed) {
                            throw ValidationException::withMessages([
                                'email' => __("Your IP address is not allowed to access this system."),
                            ]);
                        }
                    }

                    $id = $user->id;
                    break;
                }
            }
        }
        else
        {
            throw ValidationException::withMessages([
                'email' => __("this email doesn't match"),
            ]);
        }

        if (! Auth::attempt(['email' =>$this->email, 'password' =>$this->password,'id'=>$id], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited()
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
     *
     * @return string
     */
    public function throttleKey()
    {
        return Str::lower($this->input('email')).'|'.$this->ip();
    }

    /**
     * Get real client IP address (not localhost)
     */
    private function getRealClientIp()
    {
        // Check for forwarded IP headers
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    
                    // Validate IP and skip private/local IPs
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        // Fallback to request IP but check if it's localhost
        $ip = $this->ip();
        
        // If it's localhost, try to get external IP
        if ($ip === '127.0.0.1' || $ip === '::1') {
            // Try to get real IP from external service
            $context = stream_context_create([
                'http' => [
                    'timeout' => 2,
                    'user_agent' => 'Mozilla/5.0'
                ]
            ]);
            
            $externalIp = @file_get_contents('https://api.ipify.org?format=text', false, $context);
            if ($externalIp && filter_var($externalIp, FILTER_VALIDATE_IP)) {
                return trim($externalIp);
            }
        }
        
        return $ip;
    }
}
