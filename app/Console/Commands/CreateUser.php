<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:create-admin-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new admin user';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $email = $this->ask("What is the users' email?");
        $password = $this->ask("What is the users' password?");

        if ($this->confirm(sprintf('Create %s with all permissions?', $email), true)) {
            try {
                $role = Role::where('name', 'Admin')->first();

                if (!$role) {
                    $role = Role::create(['name' => 'Admin']);
                    $role->permissions()->attach(Permission::all());
                }

                $user = User::create([
                    'email' => $email,
                    'role_id' => $role->getKey(),
                    'password' => Hash::make($password),
                ]);
                $this->info(sprintf('token => %s', ($user->createToken($user->{'email'}))->plainTextToken));
            } catch (\Exception $exception) {
                $this->error($exception);
            }
        }
    }
}
