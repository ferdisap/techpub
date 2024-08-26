<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name_register' => ['required', 'string', 'max:255'],
            'email_register' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class.',email'],
            'password_register' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // dd($request->password_register, $request->name_register, $request->email_register);

        // $user = User::create([
        //     'name' => $request->name_register,
        //     'email' => $request->email_register,
        //     'password' => Hash::make($request->password_register),
        // ]);
        $user = User::where('email','nami@example.com')->first();

        event(new Registered($user));

        Auth::login($user);

        // jika previous route hanya untuk guest (eg: route('register')) maka ke HOME (dashboard)
        // jika previous route bukan untuk guest/bebas maka back() saja
        $previous_route = app('router')->getRoutes()->match($request->create(url()->previous()));
        if(in_array('guest', $previous_route->gatherMiddleware())){
          return redirect(RouteServiceProvider::HOME);
        } else {
          return back()->withInput(); 
        }
    }
}
