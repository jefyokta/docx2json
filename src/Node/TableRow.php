<?php

namespace Jefyokta\Docx2json\Node;

class TableRow extends BaseNode
{

    /**
     * @var TableCell[]
     */
    private $cells = [];
    private $widths = [];
    protected string $name = "tableRow";

    private $cellRowspan = [];


    public function assert(): bool
    {
        return $this->rootNode->nodeName == "w:tr";
    }
    protected function parse()
    {
        $this->removeMergeCell();

        if (!$this->content) {
            $this->content = [];
        }

        $lastIndex = 0;

        foreach ($this->cells as $i => $cell) {
            $colspan = (int) $cell->getColspan();
            $take = $colspan; 

            $w = [];

            for ($c = 0; $c < $take; $c++) {
                $w[] = $this->widths[$lastIndex] ?? 0;
                $lastIndex++;
            }

            $cell->setWidths($w);

            $this->content[] = $cell->render()->getJsonArray();
        }
    }

    function setWidths($width = [])
    {
        $this->widths = $width;
    }
    function appendCell(TableCell $cell)
    {

        $this->cells[] = $cell;
    }

    function cell($index = 0)
    {
        return $this->cells[$index] ?? null;
    }

    public function checkForRowSpan()
    {
        foreach ($this->cells as $i => $cell) {
            if ($cell->isRowspanStart()) {
                $this->cellRowspan[] = $i;
            }
        }
    }
    function colsHasRowSpan()
    {
        return !empty($this->cellRowspan);
    }

    /**
     * 
     * return index of column(s) that has rowspan or null
     * @return null|int[]
     */

    function getColsHasRowSpan()
    {
        return empty($this->cellRowspan) ? null : $this->cellRowspan;
    }
    function isCellMergerIn($index)
    {
        return $this->cell($index)?->isRowMerge();
    }
    function isEndOfRowSpan($cellIndex, ?TableRow $nextRow = null)
    {
        if (!$nextRow) {
            return true;
        }
        return !$nextRow->isCellMergerIn($cellIndex);
    }



    function mutateAllCellsToHeader()
    {
        foreach ($this->cells as &$cell) {
            $cell->mutateToHeader();
        }
    }

    private function removeMergeCell()
    {
        $cells = [];
        foreach ($this->cells as $cell) {
            if (!$cell->isRowMerge()) {
                $cells[] = $cell;
            }
        }

        $this->cells = $cells;
    }

    function getMaxRowspan()
    {
        $max = 1;
        foreach ($this->cells as $cell) {
            $max = $max < ($n = $cell->getRowspan()) ? $n : $max;
        };
        return $max;
    }
};
