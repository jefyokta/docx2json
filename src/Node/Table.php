<?php

namespace Jefyokta\Docx2json\Node;

use Jefyokta\Docx2json\Parser;
use Jefyokta\Docx2json\Utils\Element;

class Table extends BaseNode
{

    /**
     * @var TableRow[]
     */
    private  $rows = [];
    protected string $name = "table";
    private $widths = [];
    private $inFigure = false;



    protected function parse()
    {

        $gCols =   Element::create($this->rootNode)->querySelectorAll("w:gridCol");
        foreach ($gCols as $gCol) {
            if ($width = $gCol->getAttribute("w:w")) {
                $this->widths[] = (int)$width;
            };
        }
        $this->collectRows();
        $this->renderRows();
    }



    function collectRows()
    {
        foreach ($this->rootNode->childNodes  as $child) {
            if (($row = new TableRow($child))->assert()) {
                $row->setWidths($this->widths);
                foreach ($child->childNodes as $tc) {
                    if (($cell = new TableCell($tc))->assert()) {
                        $row->appendCell($cell);
                    }
                }
                $this->rows[] = $row;
            }
        }
    }



    private function renderRows()
    {
        if (!$this->content) {
            $this->content = [];
        }

        $starters = [];
        $activeSpans = [];

        foreach ($this->rows as $i => $row) {
            $row->checkForRowSpan();

            $rsCols = $row->getColsHasRowSpan();
            if ($rsCols) {
                foreach ($rsCols as $col) {
                    $start = [
                        'row'   => $i,
                        'cell'  => $col,
                        'total' => 1,
                    ];
                    $starters[] = $start;
                    $activeSpans[$col] = &$starters[array_key_last($starters)];
                }
            }

            foreach ($activeSpans as $cellKey => &$span) {
                $nextRow = $this->rows[$span['total'] + $span['row']] ?? null;

                if (!$row->isEndOfRowSpan($span['cell'], $nextRow)) {
                    $span['total']++;
                } else {
                    $starterRow = $this->rows[$span['row']];
                    $starterRow->cell($span['cell'])
                        ->setRowSpan($span['total']);

                    unset($activeSpans[$cellKey]);
                }
            }
        }

        $rowHeader = $this->rows[0] ?? null;
        if ($rowHeader && $this->inFigure) {
            $maxHeaderRows = $rowHeader->getMaxRowspan();

            foreach ($this->rows as $i => &$row) {
                if ($i > $maxHeaderRows) {
                    break;
                }
                $row->mutateAllCellsToHeader();
            }
        }

        $this->content = array_map(
            fn($r) => $r->render()->getJsonArray(),
            $this->rows
        );
    }


    public function assert(): bool
    {
        return $this->rootNode->nodeName == "w:tbl";
    }

    function insideFigure(){
        $this->inFigure =true;
    }
}
