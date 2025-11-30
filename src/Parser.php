<?php

namespace Jefyokta\Docx2json;

use DOMDocument;
use DOMElement;
use Jefyokta\Docx2json\Context\Citation as CitationContext;
use Jefyokta\Docx2json\Context\Heading as HeadingContext;
use Jefyokta\Docx2json\Exception\StyleOrReelsUndifined;
use Jefyokta\Docx2json\Node\Image;
use Jefyokta\Docx2json\Node\BaseNode;
use Jefyokta\Docx2json\Node\Cite;
use Jefyokta\Docx2json\Node\FigCaption;
use Jefyokta\Docx2json\Node\Heading;
use Jefyokta\Docx2json\Node\OrderedList;
use Jefyokta\Docx2json\Node\Paragraph;
use Jefyokta\Docx2json\Node\Table;
use Jefyokta\Docx2json\Node\Text;
use Jefyokta\Docx2json\Utils\Element;
use ZipArchive;

class Parser
{
    static ?DOMDocument $styles = null;
    static ?DOMDocument $rels = null;
    static ?DOMDocument $document = null;
    private $documentType = 'proposal';
    private static $chapters;
    public static ?string $documentPath;

    private $proposalChapters = [
        "pendahuluan" => [],
        "landasan_teori" => [],
        "metode_penelitian" => [],
        "jangkaan_hasil" => []

    ];

    public static $headings = [];


    private $maxChapter = 4;

    private $chapterId;

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
        $doc && static::$documentPath = $doc;

        if (!self::$chapters) {
            self::$chapters =  $this->documentType == 'proposal' ? $this->proposalChapters : $this->thesisChapters;
        }

        $this->extractDocument($doc);
        if (is_null(static::$styles) || is_null(static::$rels)) {
            throw new StyleOrReelsUndifined("styles or rels is not defined: ensure you had entered the docx path at at prev instance");
        }

        $this->collectHeading();
    }

    function setMaxChapter(int $count)
    {

        $this->maxChapter = $count;;
    }

    public static function reset()
    {
        static::$document = null;
        static::$styles = null;
        static::$rels = null;
        HeadingContext::reset();

        CitationContext::reset();
    }

    protected  function extractDocument($doc = null)
    {
        if (!$doc) return;

        $zip = new ZipArchive;
        $zip->open($doc);

        $documentXml = $zip->getFromName('word/document.xml');
        $stylesXml   = $zip->getFromName('word/styles.xml');
        $relsXml     = $zip->getFromName('word/_rels/document.xml.rels');
        $zip->close();

        static::$document = new DOMDocument();
        static::$document->loadXML($documentXml);

        static::$styles = new DOMDocument();
        static::$styles->loadXML($stylesXml);



        static::$rels = new DOMDocument();
        static::$rels->loadXML($relsXml);
    }


    function export(): array
    {
        $tmp = [];
        $currentHeading = -1;
        $collected = [];

        $body = static::$document->getElementsByTagName("body")->item(0);

        if (!$body) {
            return [];
        }

        foreach ($body->childNodes as $child) {

            if (!$child || $child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }


            if ($this->isChapter($child)) {

                if (($currentHeading + 1) >= $this->maxChapter) {
                    break;
                }

                if ($currentHeading !== -1) {
                    $collected[$currentHeading]['content'] = $tmp;
                }

                $currentHeading++;


                $collected[$currentHeading] = [
                    "chapter" => trim($child->textContent),
                    "content" => []
                ];

                $tmp = [];
                continue;
            }

            if ($currentHeading > -1) {
                $tmp[] = $child;
            }
        }

        if ($currentHeading !== -1) {
            $collected[$currentHeading]['content'] = $tmp;
        }
        $collected = array_map(function ($item) {
            $item['content'] = $this->parse($item['content']);
            return $item;
        }, $collected);

        return $collected;
    }



    protected function isChapter(DOMElement $node)
    {

        $node->nodeName == "w:p";
        $pstyle =  Element::create($node)->querySelector("w:pStyle");
        // var_dump($this->chapterId);
        return $pstyle && ($h = $pstyle->getAttribute("w:val"))
            && $h == $this->chapterId;
    }

    public function parse(iterable $nodes): array
    {
        $children = [];

        /** @var \DOMElement */
        $ignore = 0;
        foreach (($nodes) as $child) {
            if (!$child || $child->nodeType !== XML_ELEMENT_NODE) continue;
            if ($ignore > 0) {
                $ignore--;
                continue;
            }
            if ($child->nodeName == 'w:p') {
                foreach ($this->getParserClasses()["start_with_p"] as $class) {
                    $parser = new $class($child);
                    if ($parser->assert()) {
                        $children[] =   $parser->render()->getJsonArray();
                        $ignore += $parser->ignoreNext;
                        break;
                    }
                }
            } else {
                foreach ($this->getParserClasses()['stand_alone'] as $class) {
                    $parser = new $class($child);
                    if ($parser->assert()) {
                        $ignore += $parser->ignoreNext;
                        $children[] =   $parser->render()->getJsonArray();
                    }
                }
            }
        }
        return $children;
    }
    /**
     * @return array<"start_with_p"|"stand_alone",class-string<BaseNode>[]>
     */
    function getParserClasses(): array
    {
        $childOfp =  [Image::class, Heading::class, FigCaption::class, Cite::class, OrderedList::class,  Paragraph::class];
        $standAlone = [Table::class, Cite::class, OrderedList::class, Text::class];

        return [
            "start_with_p" => $childOfp,
            "stand_alone" => $standAlone
        ];
    }

    private function collectHeading()
    {

        foreach (static::$styles->getElementsByTagName("style") as $node) {
            $id = $node->getAttribute("w:styleId");

            $outline = $node->getElementsByTagName("outlineLvl");
            if ($outline->length > 0) {
                $lvl = (int)$outline->item(0)->getAttribute("w:val");
                if ($lvl == 0) {
                    $this->chapterId = $id;
                }
                if (($lvl > 0  && $lvl + 1 <= 4)) {
                    HeadingContext::set($id, $lvl + 1);
                }
            }
        }
    }
}
