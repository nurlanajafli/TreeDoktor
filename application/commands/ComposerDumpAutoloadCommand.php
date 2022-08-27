<?php

namespace application\commands;

use application\core\PackageManifest;
use Illuminate\Console\Command;


class ComposerDumpAutoloadCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'composer:dump-autoload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild the cached package manifest';

    /**
     * Execute the console command.
     *
     * @param PackageManifest $manifest
     * @return void
     */
    public function handle(PackageManifest $manifest)
    {
        if (file_exists($servicesPath = app()->getCachedServicesPath())) {
            @unlink($servicesPath);
        }

        if (file_exists($packagesPath = app()->getCachedPackagesPath())) {
            @unlink($packagesPath);
        }

        $this->info('Cache cleaned successfully.');
    }
}
