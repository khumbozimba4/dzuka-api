<?php

namespace Auth;

use App\Models\User;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_login_user(): void
    {
        $user = User::factory()->create([
            'email' => 'samsonchidobvu@gmail.com'
        ]);
        $payload = [
            'email' => $user->{'email'},
            'password' => 'rayjoyce265'
        ];
        $response = $this->post('api/login', $payload);
        $response->assertOk();
    }

}
