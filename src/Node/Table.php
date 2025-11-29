<?php

namespace Jefyokta\Docx2json\Node;

use Jefyokta\Docx2json\Parser;

class Table extends BaseNode
{

    private  $rows = [];
    protected string $name = "table";



    public  function parse()
    {

        $grid = $this->rootNode->getElementsByTagName("w:tblGrid");
        $this->collectRows();
        $this->renderRows();
    }



    function collectRows()
    {
        foreach ($this->rootNode->childNodes  as $child) {

            if ($child->nodeName == "w:tr") {
                $this->rows[] = $child;
            }
        }
    }

    private function renderRows()
    {
        $rows = [];
        /** @var \DOMElement */
        foreach ($this->rows as $row) {
            $rowJson = ["type" => "tableRow", "content" => []];

            foreach ($row->childNodes as $i => $cell) {
                $cellJson = ["type" => "tableCell", "content" => (new Parser)->parse($cell->childNodes)];

                $rowJson["content"][] = $cellJson;
            }
            $rows[] = $rowJson;
        }

        $this->content = $rows;
    }
    public function assert(): bool
    {
        return $this->rootNode->nodeName == "w:tbl";
    }

}
