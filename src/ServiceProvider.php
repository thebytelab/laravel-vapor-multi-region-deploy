<?php

namespace TheByteLab\VaporMultiRegionDeploy;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use TheByteLab\VaporMultiRegionDeploy\Console\Commands\MultiRegionDeploy;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Register the multi-region deploy command.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands(MultiRegionDeploy::class);
        }
    }
}