<?php

namespace Tests;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\NewAccessToken;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    public User $user;
    public NewAccessToken $userToken;

    protected function setUp(): void
    {
        parent::setUp();
    }


    protected function login(): self
    {
        /** @var Role $role */
        $role = Role::factory()->create();
        $this->user = User::factory()->create(['role_id' => $role->getKey()]);
        $this->seed(PermissionSeeder::class);
        $role->permissions()->attach(Permission::all());

        $this->userToken = $this->user->createToken($this->user->{'name'});
        $this->withHeaders([
            'Authorization' => sprintf('Bearer %s', $this->userToken->plainTextToken),
            'Accept' => 'application/json'
        ]);
        return $this;
    }
}
