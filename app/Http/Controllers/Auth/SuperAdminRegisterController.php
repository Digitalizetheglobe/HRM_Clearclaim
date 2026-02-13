<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class SuperAdminRegisterController extends Controller
{
    /**
     * Display the super admin registration view.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        // Check if super admin already exists
        $existingSuperAdmin = User::where('type', 'super admin')->first();
        if ($existingSuperAdmin) {
            return redirect()->route('login')
                ->with('error', 'A super admin account already exists. Only one super admin account is allowed.');
        }

        return view('auth.super-admin-register');
    }

    /**
     * Handle an incoming super admin registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        // Check if super admin already exists first
        $existingSuperAdmin = User::where('type', 'super admin')->first();
        if ($existingSuperAdmin) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'A super admin account already exists. Only one super admin account is allowed.')
                ->withErrors(['email' => 'A super admin account already exists.']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => 'super admin',
            'lang' => 'en',
            'email_verified_at' => now(),
            'created_by' => 0,
        ]);

        // Assign super admin role
        $role = Role::findByName('super admin');
        $user->assignRole($role);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
