<?php

namespace Jefyokta\Docx2json\Node;

use Jefyokta\Docx2json\Context\Heading as HeadingContext;
use Jefyokta\Docx2json\Parser;
use Jefyokta\Docx2json\Utils\Element;

class Heading extends BaseNode
{
    protected string $name = "heading";

    public function parse()
    {
        $runners = Element::create($this->rootNode)->querySelectorAll("w:r");
        foreach ($runners as $i => $runner) {
            $text = trim($runner->textContent ?? '');
            if ($text == '') {
                continue;
            }
            $isPureCounter = preg_match('/^([IVXLCDM]+\.)?(\d+\.)*(\d+)?$/i', $text);
            if ($isPureCounter) {
                unset($runners[$i]);
                break;
            }

            if (preg_match('/^(\s*)?([IVXLCDM]+\.)?(\d+\.)*(\d+)?(\s+)/i', $text)) {
                $runners[$i]->textContent = preg_replace('/^(\s*)?([IVXLCDM]+\.)?(\d+\.)*(\d+)?(\s+)/i', '', $text);
            }
        }


        $this->content = (new Parser)->parse($runners);
    }

    public function assert(): bool
    {
        $pstyle =  Element::create($this->rootNode)->querySelector("w:pStyle");
        if ($pstyle && $h = $pstyle->getAttribute("w:val")) {
            if (!HeadingContext::has($h)) {
                return false;
            }
            $this->attrs->level = HeadingContext::getLevel($h);
            return true;
        }
        return false;
    }
};
