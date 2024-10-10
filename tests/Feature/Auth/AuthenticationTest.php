<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        // $user = User::factory()->create();
        $user = \App\Models\User::factory()->create();
        // $user = User::find(1) ?? User::factory()->create();

        // $user ? $this->assertTrue(true) : $this->assertFalse(false);
        // return;

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_spa_login()
    {
      // "message": "login success",
      // "token_type": "Bearer",
      // "access_token": "1|6w0GugncG9yIrT5Mzgtx9mbCtxC4iDJmoez9zOao3330ce76";
      $user = \App\Models\User::factory()->create();    
      $response = $this->post('/api/login', [
        'email' => $user->email,
        'password' => 'password',
      ]);    
      $this->assertAuthenticated();
      $response->assertStatus(200);
    }
}
