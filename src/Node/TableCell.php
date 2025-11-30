<?php

namespace Jefyokta\Docx2json\Node;

use Jefyokta\Docx2json\Utils\Element;

class TableCell extends BaseNode
{
    protected string $name = "tableCell";
    private $isRowspanStart = false;

    private $widths = [];

    public function assert(): bool
    {
        return $this->rootNode->nodeName == "w:tc";
    }
    protected function parse()
    {
        $this->attrs->colspan = $this->getColspan();;


        $el = Element::create($this->rootNode);
        $properties = $el->querySelector("w:tcPr");

        if ($properties) {
            $elProp = Element::create($properties);
            $width = [];

            foreach ($elProp->querySelectorAll("w:tcW") as $dom) {
                if (($w = $dom->getAttribute("w:w")) !== "0") {
                    $width[] = $w;
                }
            }


            $colspan = ($el->querySelector("w:gridSpan")?->getAttribute("w:val")) ?? 1;
            $this->isRowspanStart = ($el->querySelector("w:vMerge")?->getAttribute("w:val") == "restart");
            $this->attrs->colspan = $colspan;
            $this->attrs->rowspan = $this->attrs->rowspan ? $this->attrs->rowspan : 1;
            $this->attrs->width = count($width) !== 0 ? $width : $this->widths;;
        }
    }

    function getRowspan()
    {

        return $this->attrs->rowspan ?? 1;
    }

    function isRowMerge()
    {
        $merge = $this->element()->querySelector("w:vMerge");
        if (!$merge) {
            return false;
        }
        return !$merge->getAttribute("w:val") || $merge->getAttribute("w:val") !== "restart";
    }

    function setRowSpan($rowspan = 1)
    {
        $this->attrs->rowspan = $rowspan;
    }
    function isRowspanStart()
    {

        return $this->isRowspanStart;
    }
    function mutateToHeader()
    {
        $this->name = "tableHeader";
    }

    function setWidths($widths = [])
    {
        $this->widths = $widths;
    }

    function getColspan()
    {
        return $this->element()->querySelector("w:gridSpan")?->getAttribute("w:val") ?? 1;;
    }



    function element()
    {

        return Element::create($this->rootNode);
    }
};
