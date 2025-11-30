<?php

namespace Jefyokta\Docx2json\Node;

use DOMElement;
use Jefyokta\Docx2json\Parser;

class Paragraph extends BaseNode
{

    protected string $name = "paragraph";
    protected $hasAttributes = false;


    public function assert(): bool
    {
        return $this->rootNode->nodeName == "w:p";
    }

    protected function parse()
    {
        $runners = [];

        foreach ($this->rootNode->childNodes as $e) {
            if ($e->nodeName !== "w:r") {
                continue;
            }
            $runners[] = $e;
        };


        $text = (new Parser())->parse($runners);
        $this->content[]=$text;

    }

    function getMarks()
    {

        return  [
            "bold"          => fn(DOMElement $node) => $this->findNode($node, "w:b"),
            "italic"        => fn(DOMElement $node) => $this->findNode($node, "w:i"),
            "underline"     => fn(DOMElement $node) => $this->findNode($node, "w:u"),
            "strike"        => fn(DOMElement $node) => $this->findNode($node, "w:strike"),
            "superscript"   => fn(DOMElement $node) => ($e = $this->findNode($node, "w:vertAlign")) && $e->getAttribute("w:val") === "superscript",
            "subscript"     => fn(DOMElement $node) => ($e = $this->findNode($node, "w:vertAlign"))  && $e->getAttribute("w:val") === "subscript",
            "code"          => fn(DOMElement $node) => $node->getElementsByTagName("w:rStyle")->item(0)?->getAttribute("w:val") === "Code",
        ];
    }


    /**
     * @return DOMElement
     */
    function findNode(DOMElement $node, $nodeNameToFind)
    {
        foreach ($node->childNodes  as $nf) {

            if ($nf->nodeName == $nodeNameToFind) {
                return $nf;
            }
        }

        return false;
    }
};
