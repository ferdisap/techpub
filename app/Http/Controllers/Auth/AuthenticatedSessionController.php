<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
  /**
   * Display the login view.
   */
  public function create(): View
  {
    return view('auth.login');
  }

  /**
   * Handle an incoming authentication request.
   */
  public function store(LoginRequest $request): RedirectResponse
  {
    $request->authenticate();

    $request->session()->regenerate();

    if($request->ajax()){
      return $this->ret2(200, ["Login success"],['redirect' => '']);
    }

    // return back()->withInput(); // kalau ini balik lagi ke view auth.login, sementara auth.login itu untuk yang belum login, sehingga nanti akan error karena recursive
    // return redirect()->intended('defaultpage');
    return redirect()->intended();
    
    // $previous_route = app('router')->getRoutes()->match($request->create(url()->previous()));
    // if(in_array('guest', $previous_route->gatherMiddleware())){
    //   return redirect()->route("welcome");
    // } else {
    //   return back()->withInput(); // kalau ini balik lagi ke view auth.login, sementara auth.login itu untuk yang belum login, sehingga nanti akan error karena recursive
    // }
  }

  /**
   * Destroy an authenticated session.
   */
  public function destroy(Request $request)
  {
    Auth::guard('web')->logout();
    
    $request->session()->invalidate();
    
    $request->session()->regenerateToken();
   
    // jika request ajax
    if($request->ajax()){
      return $this->ret2(200, ["Logout success"], ["redirect" => '']);
    }
    
    // jika previous route harus autentikasi dulu (eg: route('login)) maka ke HOME (dashboard)
    // jika previous route tidak harus auntentikasi (guest misalnya) maka back() saja
    $previous_route = app('router')->getRoutes()->match($request->create(url()->previous()));
    if(in_array('auth', $previous_route->gatherMiddleware())){
      return redirect(RouteServiceProvider::HOME);
    } else {
      return back()->withInput(); 
    }
  }
}
