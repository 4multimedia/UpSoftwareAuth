<?php

namespace Upsoftware\Auth\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Upsoftware\Auth\Models\Role;
use Upsoftware\Auth\Models\User;

class UpSoftwareMakeUser extends Command
{
    protected $signature = 'upsoftware:make.user';

    public function handle()
    {
        $email = $this->ask('Address e-mail');
        $password = $this->ask('Password');
        $name = $this->ask('Name');

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password)
        ]);

        $this->info('User was created');
    }
}
