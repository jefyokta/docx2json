<?php

namespace Jefyokta\Docx2json\Node;

use DOMElement;
use Jefyokta\Docx2json\Exception\InvalidNode;
use Jefyokta\Docx2json\Node\Attributes;
use Jefyokta\Docx2json\Parser;

abstract class BaseNode
{

    protected string $name;
    protected Attributes $attrs;
    protected  $content = null;
    public $childOfP = true;
    public $hasChildren = true;

    public bool $ignoreNext = false;

    /**
     * tag: <w:p>
     * @param  $node
     * 
     */
    public function __construct(protected DOMElement $node)
    {

        $this->attrs = new Attributes;
    }

    function render(): static
    {
        if (!$this->assert()) {
            throw new InvalidNode("{$this->node->nodeName} is not competible!");
        }
        $this->parse();
        return $this;
    }
    function  parse() {}

    function getJsonArray()
    {
        return [
            "type" => $this->name,
            "attrs" => $this->attrs->toArray(),
            "content" => $this->content ? $this->content : ($this->hasChildren ? (new Parser())->parse($this->node) : [])
        ];
    }

    function assert(): bool
    {

        return false;
    }
};
