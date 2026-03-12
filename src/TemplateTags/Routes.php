<?php
namespace Tonka\Spark\TemplateTags;

use Clicalmani\Foundation\Resources\TemplateTag;
use Clicalmani\Routing\Memory;
use Clicalmani\Routing\Routing;
use Inertia\Inertia;

class Routes extends TemplateTag
{
    /**
     * Tag expression
     * 
     * @var string
     */
    protected string $tag = '@routes';

    /**
     * Render a tag
     * 
     * @return string
     */
    public function render() : string
    {
        $ret = [];

        foreach (Memory::getRoutes() as $verb => $routes) {
            foreach ($routes as $route) {
                if (!$route->name) continue;
                $ret[$route->name] = $route;
            }
        }
        
        return json_encode([
            'url' => rtrim(app()->getUrl(), '/'),
            'port' => parse_url(client_uri(), PHP_URL_PORT) ?? null,
            'defaults' => [],
            'routes' => $ret
        ]);
    }
}