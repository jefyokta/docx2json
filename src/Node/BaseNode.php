<?php

namespace Jefyokta\Docx2json\Node;

use DOMElement;
use Jefyokta\Docx2json\Exception\InvalidNode;
use Jefyokta\Docx2json\Node\Attributes;
use Jefyokta\Docx2json\Parser;

/**
 * @template TAttr
 */
abstract class BaseNode
{

    protected string $name;
    /**
     * @var Attributes<TAttr>
     */
    protected Attributes $attrs;
    protected  $content = null;
    public $childOfP = true;
    public $hasChildren = true;

    /**
     * @var array|false
     */
    protected $marks = false;
    /**
     * @var string | false
     */
    protected $text = false;

    protected $hasAttributes = true;

    public bool   $isGroup = false;

    public int $ignoreNext = 0;

    /**
     * tag: <w:p>
     * @param  $node
     * 
     */
    public function __construct(protected DOMElement $rootNode)
    {
        // $this->node = $node;
        $this->attrs = new Attributes;
    }

    function render(): static
    {
        if (!$this->assert()) {
            throw new InvalidNode("{$this->rootNode->nodeName} is not competible!");
        }
        $this->parse();
        return $this;
    }
    protected function  parse() {}

    function getJsonArray()
    {
        $json =  [
            "type" => $this->name,
        ];
        if (false !== $this->hasChildren) {
            $json['content'] =  $this->content ? $this->content : ($this->hasChildren ? (new Parser())->parse($this->rootNode->childNodes) : []);
        }
        if (false !== $this->marks) {
            $json['marks'] = $this->marks;
        }

        if ($this->hasAttributes) {
            $json["attrs"] = $this->attrs->toArray();
        }
        if (is_string($this->text)) {
            $json["text"] = $this->text;
        }
        return $json;
    }

    function assert(): bool
    {

        return false;
    }
};
