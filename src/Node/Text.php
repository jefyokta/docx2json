<?php

namespace Jefyokta\Docx2json\Node;

use DOMElement;
use Jefyokta\Docx2json\Utils\Element;

class Text extends BaseNode
{



    protected string $name = "text";
    protected $hasAttributes = false;
    public $hasChildren = false;
    protected $marks = [];
    private $prefix = "w";

    public function assert(): bool
    {
        $this->text = $this->rootNode->textContent;

        return  $this->rootNode->nodeName == $this->prefix . ":r";
    }

    protected function parse()
    {

        $el = Element::create($this->rootNode);
        foreach ($this->getMarks() as $key => $handler) {
            if ($handler($el)) {
                if (!$this->marks) {
                    $this->marks = [];
                }

                $this->marks[] = ["type" => $key];
            }
        };
    }

    function getText()
    {

        return $this->getJsonArray()["text"] ?? "";
    }

    function getAsLatex(): string
    {
        $data = $this->getJsonArray();
        $text = $data['text'];

        if (trim($text) == "") {
            return "";
        }

        $marks = $data['marks'];

        $latex = $text;

        foreach ($marks as $markType) {
            $latex .= $this->getMarkLatex($markType['type'], $latex);
        }

        return $latex;
    }
    function asMath()
    {
        $this->prefix = "m";
        return $this;
    }

    private function getMarkLatex(string $type, string $content): string
    {  

        return match ($type) {
            "bold"          => "\\textbf{" . $content . "}",
            "italic"        => "\\textit{" . $content . "}",
            "underline"     => "\\underline{" . $content . "}",
            "strike"        => "\\sout{" . $content . "}",
            "superscript"   => "^{" . $content . "}",
            "subscript"     => "_{" . $content . "}",
            default         => $content,
        };
    }


    function getMarks()
    {

        return  [
            "bold"          => fn(Element $node) => $node->querySelector("w:b"),
            "italic"        => fn(Element $node) => $node->querySelector("w:i"),
            "underline"     => fn(Element $node) => $node->querySelector("w:u"),
            "strike"        => fn(Element $node) => $node->querySelector("w:strike"),
            "superscript"   => fn(Element $node) => ($e = $node->querySelector("w:vertAlign")) && $e->getAttribute("w:val") === "superscript",
            "subscript"     => fn(Element $node) => ($e = $node->querySelector("w:vertAlign"))  && $e->getAttribute("w:val") === "subscript",
            // "code"          => fn(Element $node) => ($s = $node->querySelectorAll("w:rStyle") )[0]?->getAttribute("w:val") ?? "" === "Code",
        ];
    }
}
