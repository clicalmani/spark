<?php
namespace Tonka\Spark\TemplateTags;

use Clicalmani\Foundation\Resources\TemplateTag;
use Clicalmani\Routing\Memory;
use Clicalmani\Routing\Segment;

class CurrentRoute extends TemplateTag
{
    /**
     * Tag expression
     * 
     * @var string
     */
    protected string $tag = '@currentRoute';

    /**
     * Render a tag
     * 
     * @return string
     */
    public function render() : string
    {
        $route = null;

        if ($current = Memory::currentRoute() AND $current->name) {
            $route = [
                'name' => $current->name,
                'uri' => $current->uri,
                'parameters' => array_map(fn(Segment $segment) => $segment->name, $current->getParameters()),
                'methods' => [$current->verb]
            ];
        }
        
        return json_encode($route);
    }
}