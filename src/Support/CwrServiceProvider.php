<?php

namespace LabelTools\PhpCwrExporter\Support;

use Illuminate\Support\ServiceProvider;
use LabelTools\PhpCwrExporter\CwrExporter;
use LabelTools\PhpCwrExporter\Contracts\VersionInterface;

class CwrServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Merge package config
        $this->mergeConfigFrom(__DIR__ . '/../../config/cwr.php', 'cwr');

        // Bind the exporter singleton
        $this->app->singleton(CwrExporter::class, function ($app) {
            $version = $app['config']->get('cwr.version', '2.2');
            $class   = "LabelTools\\PhpCwrExporter\\Version\\V" . str_replace('.', '', $version) . "\\Version";

            return new CwrExporter($app->make($class));
        });

        // Also bind a short alias
        $this->app->alias(CwrExporter::class, 'cwr.exporter');
    }

    public function boot()
    {
        // Publish the config file
        $this->publishes([
            __DIR__ . '/../../config/cwr.php' => config_path('cwr.php'),
        ], 'cwr-config');
    }
}