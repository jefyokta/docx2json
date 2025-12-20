<?php

namespace Jefyokta\Docx2json\Node;

use Jefyokta\Docx2json\Parser;
use Jefyokta\Docx2json\Utils\Element;

class ListItem extends BaseNode
{
    protected string $name = "listItem";

    public function assert(): bool
    {
        $numPr = Element::create($this->rootNode)->querySelector("w:numPr");
        if (!$numPr) return false;

        $numId = Element::create($numPr)->querySelector("w:numId")?->getAttribute("w:val");
        return isset($numId);
    }
    protected function parse()
    {

        $parser = new Parser();
        $content = $parser->parse($this->rootNode->childNodes);
        $this->content = [];
        $par = [];

        foreach ($content as $i => $json) {
            if ((count($par) > 0) && (!isset($content[$i + 1]) || (isset($content[$i + 1]) && $content[$i + 1]['type'] !== 'text'))) {
                $this->content[] = ["type" => "paragraph", "content" => $par];
                $par = [];
            }
            if ($json["type"] == "text") {
                $par[] = $json;
            } else {
                $this->content[] = $json;
            }
        }
    }

    public function getJsonArray()
    {
        $dirty = parent::getJsonArray();
        $start = 0;
        $cleanChildren = [];
        $ignore = 0;
        foreach ($dirty["content"] as $i => $child) {
            if ($i !== 0 && $i <= $ignore) {
                continue;               
            }

            if ($i == $start) {

                if ($this->shouldWrap($child['type'])) {
                    $startWrap = $i;
                    $wrapped = [];
                    while (isset($dirty['content'][$startWrap]) && $this->shouldWrap($dirty['content'][$startWrap])) {
                        $wrapped[] = $child;
                        $startWrap++;
                    }
                    if ($wrapped = $this->wrap($child)) {
                        $cleanChildren[] = $wrapped;
                        $ignore = $startWrap; 
                    };

                }
                if ($child['type'] !== 'paragraph' && empty($child["content"])) {
                    $start++;
                    continue;
                }
                if (in_array($child['type'], $this->allowedContent())) {
                    $cleanChildren[] = $child;
                }
            }
        }

        $dirty["content"] = $cleanChildren;

        return $dirty;
    }

    function shouldWrap($nodeType)
    {

        return in_array($nodeType, ["text", "cite", "code"]);
    }

    private function wrap(...$textNode)
    {
        if (empty($textNode)) {
            return null;
        }
        return [
            "type" => "paragraph",
            "content" => [...$textNode]
        ];
    }


    function allowedContent()
    {
        return ["paragraph", "figureTable", "imageFigure", "orderedList", "blockMath", "table", "image", "codeBlock"];
    }
}
