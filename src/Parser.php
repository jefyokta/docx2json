<?php

namespace Jefyokta\Docx2json;

use DOMDocument;
use DOMElement;
use DOMNode;
use Error;
use Jefyokta\Docx2json\Exception\StyleOrReelsUndifined;
use Jefyokta\Docx2json\Node\Image;
use ZipArchive;

class Parser
{
    static ?DOMDocument $styles = null;
    static ?DOMDocument $rels = null;
    static ?DOMDocument $document = null;
    private $documentType = 'proposal';
    private static $chapters;

    private $proposalChapters = [
        "pendahuluan" => [],
        "landasan_teori" => [],
        "metode_penelitian" => [],
        "jangkaan_hasil" => []

    ];

    private $headings = [];

    private $thesisChapters = [

        "pendahuluan" => [],
        "landasan_teori" => [],
        "metode_penelitian" => [],
        "analisa_dan_perancangan" => [],
        "hasil_dan_pembahasan" => [],
        "penutup" => []
    ];


    public function __construct($doc = null, $type = 'proposal')
    {
        $this->documentType = $type;
        if (!self::$chapters) {
            self::$chapters =  $this->documentType == 'proposal' ? $this->proposalChapters : $this->thesisChapters;
        }

        $this->extractDocument($doc);
        if (is_null(static::$styles) || is_null(static::$rels)) {
            throw new StyleOrReelsUndifined("styles or rels is not defined: ensure you had entered the docx path at at prev instance");
        }

        $this->collectHeading();
    }

    public function reset()
    {
        static::$document = null;
        static::$styles = null;
        static::$rels = null;
    }

    protected  function extractDocument($doc = null)
    {
        if (!$doc) return;

        $zip = new ZipArchive;
        $zip->open($doc);

        $documentXml = $zip->getFromName('word/document.xml');
        $stylesXml   = $zip->getFromName('word/styles.xml');
        $relsXml     = $zip->getFromName('word/_rels/document.xml.rels');
        $zip->extractTo("documents");
        $zip->close();

        static::$document = new DOMDocument();
        static::$document->loadXML($documentXml);

        static::$styles = new DOMDocument();
        static::$styles->loadXML($stylesXml);



        static::$rels = new DOMDocument();
        static::$rels->loadXML($relsXml);
    }


    public function parse(DOMNode | array $nodes): array
    {
        $children = [];

        /** @var \DOMElement */
        foreach (($nodes instanceof DOMNode ? $nodes->childNodes : $nodes) as $child) {
            if (!$child || $child->nodeType !== XML_ELEMENT_NODE) continue;

            if ($child->nodeName == 'w:p') {
                $ignore = false;

                if ($ignore) {
                    continue;
                    $ignore = false;
                }
                /** @var class-string<BaseNode> */
                foreach ($this->getParserClasses()['childOfP'] as $class) {
                    $parser = new $class($child);

                    if ($parser->assert()) {
                        $ignore = $parser->ignoreNext;
                        $children[] =   $parser->render()->getJsonArray();
                    }
                }
            }
        }
        // $this->reset();
        return $children;
    }

    function getParserClasses(): array
    {
        /** @var class-string<BaseNode>[] */
        $childOfp =  [Image::class];
        /** @var class-string<BaseNode>[] */

        $standAlone = [];
        return [
            "childOfP" => $childOfp,
            "standAlone" => $standAlone
        ];
    }


    function fillChapters()
    {
        static::$document;
    }

    private function getChapterKeys()
    {
        return array_keys($this->chapters);
    }


    private function collectHeading()
    {
        $this->headings = [];

        foreach (static::$styles->getElementsByTagName("style") as $node) {

            $id = $node->getAttribute("w:styleId");

            $outline = $node->getElementsByTagName("outlineLvl");

            if ($outline->length > 0) {
                $lvl = (int)$outline->item(0)->getAttribute("w:val");
                if (($lvl + 1 <= 4)) {
                    $this->headings[$id] = $lvl + 1;
                }
            }
        }
    }

    function collectUntilNextHeading($startIndex, $nodes)
    {
        $collected = [];

        $count = count($nodes);
        for ($i = $startIndex + 1; $i < $count; $i++) {
            $node = $nodes[$i];
            if ($node->nodeName === 'w:p') {
                $style = $this->getParagraphStyle($node);
                if ($style && str_starts_with($style, $this->getHeadingKey())) {
                    break;
                }
            }

            $collected[] = $node;
        }

        return $collected;
    }
    function getParagraphStyle(DOMElement $p)
    {
        foreach ($p->getElementsByTagName('pPr') as $pPr) {
            foreach ($pPr->getElementsByTagName('pStyle') as $style) {
                return $style->getAttribute('w:val');
            }
        }
        return null;
    }

    private function getHeadingKey($level = 1)
    {
        $k = '';
        foreach ($this->headings as $key => $value) {
            if ($value == $level) {
                $k = $key;
                break;
            }
        }



        return $k;
    }
}
