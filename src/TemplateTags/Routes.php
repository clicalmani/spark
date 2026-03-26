<?php
namespace Tonka\Spark\TemplateTags;

use Clicalmani\Foundation\Resources\TemplateTag;

class Routes extends TemplateTag
{
    /**
     * Tag expression
     * 
     * @var string
     */
    protected string $tag = '@routes\s*(?:\s*\(([0-9a-zA-Z\'"-_\/\.]*)\s*\))?';

    /**
     * Render a tag
     * 
     * @return string
     */
    public function render(array $matches) : string
    {
        app()->config->set('spark.group', trim(@$matches[1] ?? '', " '\""));
        return '';
    }
}