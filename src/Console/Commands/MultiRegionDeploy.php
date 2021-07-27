<?php

namespace TheByteLab\VaporMultiRegionDeploy\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

class MultiRegionDeploy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vapor:multi-region:deploy
                            {environment=staging : The environment name to deploy to}
                            {vapors? : Folder containing the *.vapor.yml files to use}
                            {--bin=./vendor/bin/vapor : Location of the laravel/vapor-cli executable}
                            {--commit= : (laravel/vapor-cli) The commit hash that is being deployed}
                            {--message= : (laravel/vapor-cli) The message for the commit that is being deployed}
                            {--without-waiting=false : (laravel/vapor-cli) Deploy without waiting for progress}
                            {--fresh-assets=false : (laravel/vapor-cli) Upload a fresh copy of all assets}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy your Vapor project to multiple AWS regions.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!function_exists('base_path')) {
            return $this->makeError('Function "base_path" is required by command: vapor:multi-region-deploy');
        }
        $path = base_path('vapor');
        if ($this->hasArgument('vapors')) {
            $path = $this->argument('vapors');
        }
        if (!File::exists($path)) {
            return $this->makeError('Vapors folder doesn\'t exist');
        }
        $files = $this->collectVaporFiles($path);
        if ($files->isEmpty()) {
            return $this->makeError('No vapor files found, make sure they end in ".vapor.yml"');
        }

        $environment = $this->argument('environment');

        // Check that the specified environment to deploy to exists within all
        // of the vapor files.
        foreach ($files as $vaporFile) {
            $vaporContent = Yaml::parse($vaporFile->getContents());
            if (!isset($vaporContent['environments'][$environment])) {
                return $this->makeError('"' . $vaporFile->getFilename() . '" does not contain the specified environment');
            }
        }

        // Todo: make sure the location of the 'bin' option exists.
        // Todo: do the deploy for each vapor project.
    }

    /**
     * Return error string and exit code.
     *
     * @param  string  $error
     * @return int
     */
    protected function makeError(string $error)
    {
        $this->error($error);
        return 0;
    }

    /**
     * Create a new collection of Vapor files from the provided folder.
     *
     * @param  string  $path
     * @return \Illuminate\Support\Collection
     */
    protected function collectVaporFiles(string $path)
    {
        return collect(File::files($path))->filter(function ($value, $key) {
            return Str::endsWith($value->getFilename(), '.vapor.yml');
        });
    }
}