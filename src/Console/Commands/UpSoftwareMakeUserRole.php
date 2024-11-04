<?php

namespace Upsoftware\Auth\Console\Commands;

use Illuminate\Console\Command;
use Upsoftware\Auth\Models\Role;

class UpSoftwareMakeUserRole extends Command
{
    protected $signature = 'upsoftware:make.userrole';

    public function handle()
    {
        $name = $this->ask('Role name');
        $description = $this->ask('Role description');
        Role::create([
            'name' => $name,
            'description' => $description
        ]);
        $this->info('Role was created');
    }
}
