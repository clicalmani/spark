<?php
namespace Clicalmani\Foundation\Resources\Tags;

use Clicalmani\Foundation\Resources\TemplateTag;

class RoutesTag extends TemplateTag
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
        return <<<INERTIA
        <div id="app" data-page="{{ retrieveViewData() }}"></div>
        INERTIA;
    }
}