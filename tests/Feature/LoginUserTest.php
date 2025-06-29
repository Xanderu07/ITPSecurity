<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;

class LoginUserTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function login_succeeds_with_correct_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('CorrectPassword123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'CorrectPassword123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function login_fails_with_wrong_password()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('CorrectPassword123'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'test@example.com',
            'password' => 'WrongPassword456',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[Test]
    public function login_fails_when_email_does_not_exist()
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'nietbestaan@example.com',
            'password' => 'SomePassword123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
