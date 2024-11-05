<?php

namespace Upsoftware\Auth\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class UpSoftwareMigrations extends Command
{
    protected $signature = 'upsoftware:migrations.auth {--tenant}';

    public function handle()
    {
        $filesystem = new Filesystem();
        $this->info('Coping migrations...');

        $sourcePath = dirname(__FILE__).'/resources/database/migrations';
        $sourcePath = strtr($sourcePath, ['Console/Commands/' => '']);

        if (!$filesystem->exists($sourcePath)) {
            $this->error("Katalog {$sourcePath} nie istnieje");
            return;
        }

        if (in_array('--tenant', $_SERVER['argv'])) {
            $destinationPath = database_path('migrations/tenant');
        } else {
            $destinationPath = database_path('migrations');
        }

        if (!$filesystem->exists($destinationPath)) {
            $filesystem->makeDirectory($destinationPath, 0755, true);
        }

        $filesystem->copyDirectory($sourcePath, $destinationPath);

        $this->info("Files was coping {$destinationPath}.");
    }
}
