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


        $this->content = (new Parser())->parse($runners);
    

    }


};
