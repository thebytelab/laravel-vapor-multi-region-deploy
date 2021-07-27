<?php

namespace TheByteLab\VaporMultiRegionDeploy\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
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
                            {--vapors= : Folder containing the *.vapor.yml files to use}
                            {--bin= : Location of the laravel/vapor-cli executable}
                            {--commit= : (laravel/vapor-cli) The commit hash that is being deployed}
                            {--message= : (laravel/vapor-cli) The message for the commit that is being deployed}
                            {--without-waiting : (laravel/vapor-cli) Deploy without waiting for progress}
                            {--fresh-assets : (laravel/vapor-cli) Upload a fresh copy of all assets}';

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
            return $this->makeError('Function "base_path" is required, try running "composer install"');
        }
        $path = base_path('vapor');
        if ($this->hasOption('vapors') && !is_null($this->option('vapors'))) {
            $path = $this->option('vapors');
        }
        if (!File::exists($path)) {
            return $this->makeError('Vapors folder does not exist');
        }
        $files = $this->collectVaporFiles($path);
        if ($files->isEmpty()) {
            return $this->makeError('No vapor files found, make sure they end in ".vapor.yml"');
        }
        $bin = base_path('vendor/bin/vapor');
        if ($this->hasOption('bin') && !is_null($this->option('bin'))) {
            $bin = $this->option('bin');
        }
        if (!File::exists($bin)) {
            return $this->makeError('Vapor executable (--bin) does not exist');
        }

        $environment = $this->argument('environment');

        // Check that the specified environment to deploy to exists within all
        // of the vapor files.
        foreach ($files as $vaporFile) {
            $vaporContent = Yaml::parse($vaporFile->getContents());
            if (!isset($vaporContent['environments'][$environment])) {
                return $this->makeError('"' . $vaporFile->getFilename() . '" does not contain the "' . $environment . '" environment');
            }
        }

        $command = [$bin, 'deploy', $environment];
        if ($this->option('commit')) {
            $command[] = '--commit=' . $this->option('commit');
        }
        if ($this->option('message')) {
            $command[] = '--message' . $this->option('message');
        }
        if ($this->option('without-waiting')) {
            $command[] = '--without-waiting';
        }
        if ($this->option('fresh-assets')) {
            $command[] = '--fresh-assets';
        }

        // Run the deployment for each Vapor file, one after the other.
        foreach ($files as $vaporFile) {
            $this->info('Starting deployment with manifest: ' . $vaporFile->getFilename());
            $this->newLine();
            $process = new Process(array_merge($command, ['--manifest=' . $vaporFile->getPathname()]));
            $process->run(function ($type, $buffer) {
                echo $buffer;
            });
            $this->newLine();

            if (!$process->isSuccessful()) {
                $this->error('Deployment unsuccessful with manifest: ' . $vaporFile->getFilename());
            }
        }
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
        return 1;
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
