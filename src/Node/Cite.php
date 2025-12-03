<?php

namespace Jefyokta\Docx2json\Node;

use DOMElement;
use Jefyokta\Docx2json\Context\Citation as ContextCitation;
use Jefyokta\Docx2json\Utils\Citation;
use Jefyokta\Docx2json\Utils\Element;

class Cite extends BaseNode
{

    protected string $name = "cite";

    public function assert(): bool
    {
        if ($this->rootNode->nodeName !== "w:r") return false;

        $el = Element::create($this->rootNode);
        if ($el->querySelector("w:fldChar")?->getAttribute("w:fldCharType") == "begin") {
            return ($this->ignoreNext = $this->collectField()) > 0;
        };
        return false;
    }

    function collectField()
    {

        $ignore = 0;
        $isCite = false;
        $next = $this->rootNode;
        while ($next instanceof DOMElement) {
            $next =  $next->nextSibling;
            if (!$next) {
                break;
            }
            $ignore++;
            $el = Element::create($next);

            $instr =  $el->querySelector("w:instrText");
            if (
                $instr && str_contains($instr->textContent, "CITATION") &&
                ($jsnStart = strpos($instr->textContent ?? "", "{")) !== false
            ) {
                $json = substr($instr->textContent, $jsnStart);

                $data = json_decode($json, true);

                if (!$data) {
                    break;
                }
                $key = $data["citationID"] ?? false;

                $items = $data["citationItems"][0] ?? false;
                if (!$items) {
                    break;
                }
                $itemData = $items["itemData"] ?? false;



                if (!$key || !$itemData) {
                    break;
                }


                $citation = new Citation($key, $itemData);
                
                ContextCitation::add($citation);
                $this->attrs->cite = $key;
                $this->attrs->citeA = ($c = $data["properties"]["formattedCitation"] ?? false) && strpos($c, "(") == 0 ? true : false;
                $isCite = true;
            }

            $end =  $el->querySelector("w:fldChar")?->getAttribute("w:fldCharType") == "end";
            if ($end) {
                break;
            }
        }
        return $isCite ? $ignore : 0;
    }
}
