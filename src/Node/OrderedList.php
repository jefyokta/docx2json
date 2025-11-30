<?php

namespace Jefyokta\Docx2json\Node;

use DOMElement;
use Jefyokta\Docx2json\Utils\Element;

class OrderedList extends BaseNode
{
    protected string $name = "orderedList";


    /**
     * @var ListItem[]
     */
    private $lists = [];

    public function assert(): bool
    {

        $numPr = Element::create($this->rootNode)->querySelector("w:numPr");
        if (!$numPr) return false;


        $ilvl =  Element::create($numPr)->querySelector("w:ilvl")?->getAttribute("w:val");
        $numId = Element::create($numPr)->querySelector("w:numId")?->getAttribute("w:val");



        return isset($ilvl) && isset($numId);
    }

    protected function parse(): void
    {
        $next = $this->rootNode;

        while ($next && $next->nodeName === "w:p") {

            $item = new ListItem($next);

            if (!$item->assert()) {
                break;
            }
            $this->lists[] = $item;
            $this->ignoreNext++;
            $next = $next->nextElementSibling;
        }

        foreach ($this->lists as $list) {
            $this->content[] = $list->render()->getJsonArray();
        }
    }
}
