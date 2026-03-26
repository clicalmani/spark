<?php
namespace Tonka\Spark\TemplateTags;

use Clicalmani\Foundation\Resources\TemplateTag;
use Clicalmani\Routing\Memory;

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
    public function render(array $matches) : string
    {
        $route = null;

        if ($current = Memory::currentRoute() AND $current->name) {
            $params = [];

            /** @var \Clicalmani\Routing\Segment */
            foreach ($current->getParameters() as $segment) {
                $params[substr($segment->name, 1)] = $segment->value;
            }

            $route = [
                'name' => $current->name,
                'uri' => $current->uri,
                'parameters' => $params,
                'methods' => [$current->verb]
            ];
        }
        
        return json_encode($route);
    }
}