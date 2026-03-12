<?php
namespace Tonka\Spark;

use Clicalmani\Foundation\Providers\ServiceProvider;
use Inertia\Inertia;

class SparkServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        parent::register();
    }

    public function boot(): void
    {
        \Clicalmani\Foundation\Resources\Kernel::$template_tags = array_merge(
            \Clicalmani\Foundation\Resources\Kernel::$template_tags,
            [
                \Tonka\Spark\TemplateTags\Routes::class,
                \Tonka\Spark\TemplateTags\CurrentRoute::class,
            ]
        );
        Inertia::$rootDataAttributes['routes'] = '@routes';
        Inertia::$rootDataAttributes['currentroute'] = '@currentRoute';
    }
}