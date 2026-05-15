<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_can_login_and_is_redirected_to_cabinet(): void
    {
        $response = $this->post(route('login.submit'), [
            'email' => 'master@example.com',
            'password' => 'master123',
        ]);

        $response
            ->assertRedirect(route('cabinet'))
            ->assertSessionHas('user.role', User::ROLE_MASTER);
    }

    public function test_invalid_login_keeps_user_guest(): void
    {
        $response = $this
            ->from(route('login'))
            ->post(route('login.submit'), [
                'email' => 'master@example.com',
                'password' => 'wrong-password',
            ]);

        $response
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email')
            ->assertSessionMissing('user');
    }

    public function test_visitor_registration_creates_user_and_session(): void
    {
        $response = $this->post(route('register.submit'), [
            'name' => 'Петров Петр Петрович',
            'email' => 'visitor@example.com',
            'phone' => '+79001112233',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response
            ->assertRedirect(route('home'))
            ->assertSessionHas('user.email', 'visitor@example.com');

        $this->assertDatabaseHas('users', [
            'email' => 'visitor@example.com',
            'role' => User::ROLE_VISITOR,
        ]);
    }

    public function test_guest_middleware_redirects_authenticated_user_from_login_page(): void
    {
        $visitor = User::create([
            'name' => 'Сидоров Сидор Сидорович',
            'email' => 'sidor@example.com',
            'phone' => '+79004445566',
            'role' => User::ROLE_VISITOR,
            'password' => Hash::make('secret123'),
        ]);

        $this
            ->withSession($this->sessionFor($visitor))
            ->get(route('login'))
            ->assertRedirect(route('home'));
    }
}
