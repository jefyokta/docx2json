<?php

namespace Jefyokta\Docx2json\Utils;

use DOMElement;
use Jefyokta\Docx2json\Exception\InvalidNode;
use Jefyokta\Docx2json\Node\Text;

class OOMLTranslator
{

    public function __construct(private ?Element $element = null) {}

    static function from(DOMElement | Element $nodeOrElement)
    {
        if ($nodeOrElement instanceof DOMElement) {
            $nodeOrElement = Element::create($nodeOrElement);
        }
        return new static($nodeOrElement);
    }
    function getLatex(): ?string
    {
        if (!$this->element) {
            return null;
        }


        $latexContent = $this->parseElement($this->element->getNode());


        if (!empty(trim($latexContent))) {

            return '' . trim($latexContent) . '';
        }

        return null;
    }

    private function parseElement(DOMElement $node): string
    {

        return match ($node->nodeName) {
            'm:oMath', 'm:r' => $this->parseMathContainer($node),
            'm:t' => $this->parseMathText($node),
            'm:f' => $this->parseFraction($node),



            default => $this->parseChildrenRecursively($node),
        };
    }

    /**
     * 
     * @param DOMElement $node
     * @return string
     */
    private function parseMathContainer(DOMElement $node): string
    {
        return $this->parseChildrenRecursively($node);
    }

    /**
     * 
     * @param DOMElement $node
     * @return string 
     */
    private function parseMathText(DOMElement $node): string
    {
        try {
            return (new Text($node->parentElement))->asMath()->render()->getAsLatex();
        } catch (InvalidNode $th) {
        } finally {
            return $node->textContent;
        }
    }

    /**
     * 
     *
     *  OOML Fraction:
     *
     * @param DOMElement $node Node <m:f> .
     * @return string ex: \frac{...}{...}
     */
    private function parseFraction(DOMElement $node): string
    {
        $elementWrapper = Element::create($node);


        $numNode = $elementWrapper->querySelector('m:num');

        $denNode = $elementWrapper->querySelector('m:den');

        $numeratorLatex = $numNode ? $this->parseChildrenRecursively($numNode) : '';
        $denominatorLatex = $denNode ? $this->parseChildrenRecursively($denNode) : '';


        return '\frac{' . $numeratorLatex . '}{' . $denominatorLatex . '}';
    }

    private function parseChildrenRecursively(DOMElement $parentNode): string
    {
        $content = '';
        foreach ($parentNode->childNodes as $child) {

            if ($child instanceof DOMElement) {
                $content .= $this->parseElement($child);
            }
        }
        return $content;
    }
}
