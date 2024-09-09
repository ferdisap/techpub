<?php

namespace Tests\Feature\Auth;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'first_name_register' => 'firstname',
            'middle_name_register' => 'midname',
            'last_name_register' => 'lastname',
            'job_title_register' => 'aircraft manual',
            'enterprise_name' => 'PTDI',
            'email_register' => 'test@example.com',
            'password_register' => 'password',
            'password_register_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }
}
