<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Enterprise;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Database\Seeders\CodeSeeder;
use Database\Seeders\EnterpriseSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Str;

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
  // public function store(Request $request): RedirectResponse
  public function store(Request $request)
  { 
    $request->validate([
      'first_name_register' => ['required', 'string', 'max:255'],
      'middle_name_register' => ['string', 'max:255'],
      'last_name_register' => ['string', 'max:255'],
      'job_title_register' => ['string', 'max:255'],
      'enterprise_name' => ['required', 'string', 'max:255'],
      'email_register' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class . ',email'],
      'password_register' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $storage = Str::random(5);
    while(User::where('storage', $storage)->first()){
      $storage = Str::random(5);
    }

    $user = User::create([
      'first_name' => $request->first_name_register,
      'middle_name' => $request->middle_name_register,
      'last_name' => $request->last_name_register,
      'job_title' => $request->job_title_register,
      'storage' => $storage,
      'email' => $request->email_register,
      'password' => Hash::make($request->password_register),
    ]);


    if (!($enterprise = Enterprise::where('name', $request->enterprise_name)->first())) {
      if ($code = CodeSeeder::seed('')) {
        if (!($enterprise = EnterpriseSeeder::seed($request->enterprise_name, $code->id))) {
          $code->delete();
        }
      } else {
        $enterprise->delete();
        return abort(400);
      };
    }
    
    $user->work_in()->associate($enterprise);

    event(new Registered($user));

    Auth::login($user);

    // jika previous route hanya untuk guest (eg: route('register')) maka ke HOME (dashboard)
    // jika previous route bukan untuk guest/bebas maka back() saja
    $previous_route = app('router')->getRoutes()->match($request->create(url()->previous()));
    if (in_array('guest', $previous_route->gatherMiddleware())) {
      return redirect(RouteServiceProvider::HOME);
    } else {
      // return back()->withInput(); 
      return redirect()->intended();
    }
  }
}
