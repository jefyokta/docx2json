<?php

namespace Jefyokta\Docx2json\Node;

use DOMElement;
use Jefyokta\Docx2json\Utils\Element;

class Text extends BaseNode
{



    protected string $name = "text";
    protected $hasAttributes = false;

    private ?DOMElement $textNode;
    public function assert(): bool
    {
        $this->text = $this->rootNode->textContent;;
        return  $this->rootNode->nodeName == "w:r";
    }

    public function parse()
    {

        $el = Element::create($this->rootNode);
        foreach ($this->getMarks() as $key => $handler) {
            if ($handler($el)) {
                if (!$this->marks) {
                    $this->marks = [];
                }
                $this->marks[] = ["type" => $key];
            }
        };
    }


    function getMarks()
    {

        return  [
            "bold"          => fn(Element $node) => $node->querySelector("w:b"),
            "italic"        => fn(Element $node) => $node->querySelector("w:i"),
            "underline"     => fn(Element $node) => $node->querySelector("w:u"),
            "strike"        => fn(Element $node) => $node->querySelector("w:strike"),
            "superscript"   => fn(Element $node) => ($e = $node->querySelector("w:vertAlign")) && $e->getAttribute("w:val") === "superscript",
            "subscript"     => fn(Element $node) => ($e = $node->querySelector("w:vertAlign"))  && $e->getAttribute("w:val") === "subscript",
            "code"          => fn(Element $node) => $node->querySelectorAll("w:rStyle")[0]?->getAttribute("w:val") ?? "" === "Code",
        ];
    }
}
