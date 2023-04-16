<?php

namespace Tests\Feature\users;

use App\Models\User;
use Tests\TestCase;

class DeleteUserTest extends TestCase
{
    public function test_delete_user(): void
    {
        $user = User::factory()->create();
        $response = $this->login()->delete(sprintf('api/users/%s',$user->getKey()));
        $response->assertOk();
    }
}
