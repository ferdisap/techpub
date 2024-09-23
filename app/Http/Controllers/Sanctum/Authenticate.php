<?php 

namespace App\Http\Controllers\Sanctum;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Str;
use App\Models\Enterprise;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\CodeSeeder;
use Database\Seeders\EnterpriseSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;

class Authenticate extends Controller
{
  public function login(Request $request)
  {

    $request->validate([
      'email' => ['required', 'string', 'email'],
      'password' => ['required', 'string'],
    ]);

    if(!Auth::attempt($request->only('email', 'password'))){
      return abort(401); // unauthorize
    }

    $user = User::where('email', $request->email)->firstOrFail();

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
      'status' => 'login success',
      'access_token' => $token,
      'token_type' => 'Bearer',
    ]);
  }

  public function logout(Request $request)
  {
    // Auth::user()->tokens()->delete();
    $request->user()->currentAccessToken()->delete();
    return response()->json([
      'message' => "logout success"
    ]);
  }
}