<?php

namespace Jefyokta\Docx2json\Node;

use Jefyokta\Docx2json\Utils\Element;
use Jefyokta\Docx2json\Utils\OOMLTranslator;

class Equation extends BaseNode
{
    protected string $name = 'blockMath';
    public $hasChildren = true;

    public bool $isGroup = true;

    public function assert(): bool
    {

        ($p = Element::create($this->rootNode));
        $hasMath = $p->querySelector("oMath") ?? $p->querySelector("m:oMath") ?? $p->querySelector("m:oMathPara");


        return $hasMath ? true : false;
        return ($p = Element::create($this->rootNode)) && $this->rootNode->tagName === "oMath"
            || $this->rootNode->tagName === "m:oMath" || $this->rootNode->tagName === "m:oMathPara";
    }

    protected function parse()
    {

        $maths =  Element::create($this->rootNode)->querySelectorAll("m:oMath");

        if (!$this->content) {
            $this->content = [];
        }
        foreach ($maths as $math) {
            $this->content[] = [
                "type" => $this->name,
                "attrs" => [
                    "latex" => $latex = OOMLTranslator::from($math)->getLatex()
                ]
            ];
        }

        // $this->attrs->latex = "please \ rewrite \ your \ equation with \ \LaTeX";
    }
};
