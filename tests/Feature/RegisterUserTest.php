<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_data_and_is_redirected_to_dashboard()
    {
        $response = $this->post('/register', [
            'name' => 'TestUser',
            'email' => 'testuser@example.com',
            'password' => 'Very$trongPass987!',
            'password_confirmation' => 'Very$trongPass987!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'testuser@example.com',
            'name' => 'TestUser',
        ]);
    }

    public function test_registration_fails_with_weak_password()
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'WeakPasswordUser',
            'email' => 'weakpass@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_registration_fails_when_email_already_exists()
    {
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $response = $this->from('/register')->post('/register', [
            'name' => 'NewUser',
            'email' => 'existing@example.com',
            'password' => 'Very$trongPass987!',
            'password_confirmation' => 'Very$trongPass987!',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
