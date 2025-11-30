<?php

namespace Jefyokta\Docx2json\Node;

use DOMElement;
use finfo;
use Jefyokta\Docx2json\Parser;
use Jefyokta\Docx2json\Utils\Element;
use ZipArchive;

/**
 * @extends BaseNode<"src">
 * 
 * 
 */
class Image extends BaseNode
{

    private $shouldBeFigure = false;
    public $hasChildren = false;
    private ?DOMElement $imgNode;
    protected string $name = 'image';
    public function __construct(...$param)
    {
        parent::__construct(...$param);
        $this->attrs->src = "";
        $this->attrs->width = 0;
    }


    protected function parse()
    {

        $el = Element::create($this->imgNode);
        $blib = $el->querySelector("a:blip");
        $extent = $el->querySelector("wp:extent")
            ?? $el->querySelector("a:ext")
            ?? $el->querySelector("pic:spPr");

        if ($blib && $blib instanceof DOMElement) {
            $id = $blib->getAttribute("r:embed");
            $src = $this->getImageSource($id);
            $base64 = $this->toBase64($src);
            $this->attrs->src = $base64;
        }

        if ($extent instanceof DOMElement && $extent->hasAttribute("cx")) {
            $emuWidth = (int) $extent->getAttribute("cx");
            $pxWidth  = $emuWidth / 9525;
            $this->attrs->width = $pxWidth;
        }

        $next = $this->rootNode->nextElementSibling;
        if ($next && ($cap = new FigCaption($next))->assert()) {
            $this->shouldBeFigure = true;
            $this->name = "imageFigure";
            $this->ignoreNext = 1;
            $this->attrs->figureId = $cap->getId();
            $this->attrs->id = $cap->getId();
            $this->hasChildren = true;
            $this->content = [
                [
                    "type" => "image",
                    "attrs" => [
                        "src" => $this->attrs->src,
                        "width" => $this->attrs->width
                    ]
                ],
                $cap->render()->getJsonArray()

            ];
            $this->attrs = new Attributes;
        }
    }
    public function assert(): bool
    {
        $runner = $this->rootNode->getElementsByTagName('r');

        return $runner && $runner->item(0) && $this->imgNode = $runner->item(0)->getElementsByTagName('drawing')->item(0);
    }

    private function getImageSource($id)
    {

        foreach (Parser::$rels->getElementsByTagName("Relationship") as $r) {
            if ($r->getAttribute("Id") == $id) {
                return $r->getAttribute("Target");
            }
        };

        return null;
    }

    private function toBase64(string $img): ?string
    {
        $zip = new ZipArchive();
        if ($zip->open(Parser::$documentPath) !== true) {
            return null;
        }

        $binary = $zip->getFromName("word/{$img}");
        $zip->close();

        if ($binary === false) {
            return null;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($binary);

        $base64 = base64_encode($binary);

        return "data:$mime;base64,";
    }
}
