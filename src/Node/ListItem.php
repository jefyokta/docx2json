<?php

namespace Jefyokta\Docx2json\Node;

use Jefyokta\Docx2json\Utils\Element;

class ListItem extends BaseNode
{
    protected string $name = "listItem";

    public function assert(): bool
    {
        $numPr = Element::create($this->rootNode)->querySelector("w:numPr");
        if (!$numPr) return false;

        $numId = Element::create($numPr)->querySelector("w:numId")?->getAttribute("w:val");

        return isset($numId);
    }
    protected function parse()
    {
        Element::create($this->rootNode)->findChildNode("w:pPr")?->remove();
    }
}
