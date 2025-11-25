<?php

namespace Jefyokta\Docx2json\Node;

use DOMElement;

class Image extends BaseNode
{

    private $shouldBeFigure = true;

    protected string $name = 'image';
    
    public function render(): static
    {

       $next = $this->node->parentElement->nextElementSibling;
        return $this;
    }



}
