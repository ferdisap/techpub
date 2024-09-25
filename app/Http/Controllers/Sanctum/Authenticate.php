<?php

namespace App\Http\Controllers\Sanctum;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * if use cookie currentAccessToken() will return TransientToken class
 * if use token currentAccessToken() will return PersonalAccessToken class
 * source = https://stackoverflow.com/questions/68255192/laravel-sanctum-delete-current-user-token-not-working
 * 
 * umumnya sesuai di documentation, sanctum menggunakan cookie. Jika cookie tidak ada maka akan pakai token based di authorization header
 */
class Authenticate extends Controller
{
  /**
   * 
   * user only can create one token for each device_name. If they are multiple login the previous token wil be deleted.
   * 
   * login by using Personal Access Token
   * user must attach header Authorization: Bearer <token> for each request
   * 
   * return status code 422 Uncompressable Content if login failed
   * return status code 200 OK if login success
   */
  public function store(LoginRequest $request)
  {
    // ensure request is not rate limited
    $request->ensureIsNotRateLimited();

    // search user
    $user = User::where('email', $request->validated('email'))->first();
    if (!$user || !Hash::check($request->password, $user->password)) {
      // javscript response.data = {message:?string, errors:{email: ?Array[?string]}}
      throw new HttpResponseException(response([
        'message' => "login failed",
        'email' => ['The provided credentials are incorrect.'],
      ],422));
    }

    // create token
    $tokenName = $request->header('X-DEVICE-NAME') ?? 'auth_token';
    $abilities = ['*'];
    $token = PersonalAccessToken::where('name', $tokenName)
              ->where('tokenable_type', User::class)
              ->where('tokenable_id', $user->id)
              ->where('name', $tokenName)
              ->where('abilities', json_encode($abilities))
              ->delete();
    $token = $user->createToken($tokenName, $abilities)->plainTextToken;

    // clear the rate limiter for this request
    RateLimiter::clear($request->throttleKey());

    // return response
    return response()->json([
      'message' => 'login success',
      'token_type' => 'Bearer',
      'access_token' => $token,
    ]);
  }

  /**
   * logout by using Personal Access Token
   * user must attach header Authorization: Bearer <token> for each request
   */
  public function destroy(Request $request)
  {
    $request->user()->currentAccessToken()->delete();
    return response()->json([
      'message' => "logout success"
    ]);
  }
}
