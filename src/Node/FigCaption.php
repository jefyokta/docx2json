<?php

namespace Jefyokta\Docx2json\Node;

use DOMElement;
use Jefyokta\Docx2json\Context\Caption;
use Jefyokta\Docx2json\Parser;
use Jefyokta\Docx2json\Utils\Element;

class FigCaption extends BaseNode
{

    protected string $name = "figcaption";


    private ?Table $table;
    public function assert(): bool
    {
        $style = Element::create($this->rootNode)->styleNode();
        if ($style && $id = $style->getAttribute("w:val")) {
            return   Caption::getStyleId() == $id;
        }

        return false;
    }

    private function shoulBeMergeWithTable()
    {

        $next = $this->rootNode->nextElementSibling;

        return $next && ($this->table = new Table($next))->assert();
    }
    protected function parse()
    {
        $keep = [];
        $endIndex = -1;

        foreach ($this->rootNode->childNodes as $i => $node) {
            if (!$node instanceof DOMElement) continue;

            $fld = Element::create($node)->findChildNode("w:fldChar");
            if ($fld && $fld->getAttribute("w:fldCharType") === "end") {
                $endIndex = $i;
                break;
            }
        }

        if ($endIndex === -1) {
            foreach ($this->rootNode->childNodes as $node) {
                $keep[] = $node;
            }
            $this->content = $keep;
            return;
        }

        foreach ($this->rootNode->childNodes as $i => $node) {
            if ($i > $endIndex) {
                $keep[] = $node;
            }
        }

        $this->content = (new Parser)->parse($keep);
    }

    public function getJsonArray()
    {

        if (!$this->shoulBeMergeWithTable()) {
            return parent::getJsonArray();
        }
        $this->ignoreNext++;

        return [
            "type" => "figureTable",
            "content" => [
                parent::getJsonArray(),
                $this->table->render()->getJsonArray()
            ],
            "attrs" => [
                "id" => $this->getId(),
                "figureId" => $this->getId()
            ]
        ];
    }


    function getId()
    {
        return Element::create($this->rootNode)->querySelector("s:bookmarkStart")?->getAttribute("w:name") ?? uniqid();
    }
}
