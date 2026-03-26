<?php
namespace Tonka\Spark;

use Clicalmani\Foundation\Providers\ServiceProvider;
use Inertia\Inertia;

class SparkServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        parent::register();

        \Clicalmani\Foundation\Resources\Kernel::$template_tags = array_merge(
            \Clicalmani\Foundation\Resources\Kernel::$template_tags,
            [
                \Tonka\Spark\TemplateTags\Routes::class,
                \Tonka\Spark\TemplateTags\SparkRoutes::class,
                \Tonka\Spark\TemplateTags\CurrentRoute::class,
            ]
        );
        Inertia::$rootDataAttributes['routes'] = '@SparkRoutes';
        Inertia::$rootDataAttributes['currentroute'] = '@currentRoute';

        (new \Clicalmani\Foundation\Resources\TonkaTwigExtension)->addFunction('setSparkGroup', function (string $group) {
            app()->config->set('spark.group', $group);
        });

        foreach ([
            Console\MakeContract::class
        ] as $command) {
            app()->addCommand($command);
        }

        if ( isConsoleMode() ) {
            app()->console->make();
        }
    }

    public function boot(): void
    {
        if ( is_file(config_path('/spark.php')) ) {
            app()->config->set('spark', require_once config_path('/spark.php'));
        }
    }
}