<?php


namespace Jefyokta\Docx2json\Utils;

use DOMElement;

/**
 * Wrapper utility for DOMElement that provides helpful DOM traversal
 * methods similar to browser-based query selectors and child node search.
 *
 * This class is part of the Docx2Json internal DOM parsing utilities.
 *
 * @package Jefyokta\Docx2json\Utils
 */
class Element
{
    /**
     * Create a new Element wrapper instance.
     *
     * @param DOMElement $node The DOM element node to wrap.
     */
    public function __construct(private DOMElement $node) {}

    /**
     * Factory method to instantiate the Element wrapper.
     *
     * Useful for method chaining without manually calling `new`.
     *
     * @param DOMElement $node DOM element to wrap.
     * @return static New Element instance.
     */
    public static function create(DOMElement $node): static
    {
        return new static($node);
    }

    /**
     * Find **direct child node** by tag/node name (non-recursive).
     *
     * @param string $nodeNameToFind The node name/tag to match.
     * @return DOMElement|null The first matching direct child DOMElement or null if not found.
     */
    public function findChildNode(string $nodeNameToFind): ?DOMElement
    {
        if ($this->node->childNodes->length === 0) {
            return null;
        }

        foreach ($this->node->childNodes as $child) {
            if ($child->nodeName === $nodeNameToFind && $child instanceof DOMElement) {
                return $child;
            }
        }

        return null;
    }

    /**
     * Recursive query selector to find the **first matching descendant node**.
     *
     * Works similar to `document.querySelector()` in the browser,
     * but only matches by tag/node name.
     *
     * @param string $node The tag/node name to search for.
     * @return DOMElement|null The first matching DOMElement found in the subtree.
     */
    public function querySelector(string $nodeName): ?DOMElement
    {
        if ($this->node->childNodes->length === 0) {
            return null;
        }

        foreach ($this->node->childNodes as $child) {
            if (!$child instanceof DOMElement) {
                continue;
            }

            if ($child->nodeName === $nodeName) {
                return $child;
            }

            $found = Element::create($child)->querySelector($nodeName);
            if ($found) {
                return $found;
            }
        }

        return null;
    }

    /**
     * Recursive query selector to find **all matching descendant nodes** by tag/node name.
     *
     * Similar to `querySelectorAll()` in the browser (but only matches by tag name).
     *
     * @param string $nodeName The tag/node name to match.
     * @return DOMElement[] List of matched DOMElement nodes in document order.
     */
    public function querySelectorAll(string $nodeName): array
    {
        $list = [];

        if ($this->node->childNodes->length === 0) {
            return [];
        }

        foreach ($this->node->childNodes as $child) {
            if ($child instanceof DOMElement && $child->nodeName === $nodeName) {
                $list[] = $child;
            }

            if ($child instanceof DOMElement) {
                $list = [...$list, ...Element::create($child)->querySelectorAll($nodeName)];
            }
        }

        return $list;
    }

    /**
     * Get the raw DOMElement node that is being wrapped.
     *
     * @return DOMElement Original DOMElement instance.
     */
    public function getNode(): DOMElement
    {
        return $this->node;
    }

    /**
     * Check whether the element has a **direct child node** with the given tag name.
     *
     * @param string $nodeName The tag/node name to check.
     * @return bool True if at least one direct child matches.
     */
    public function hasChild(string $nodeName): bool
    {
        return $this->findChildNode($nodeName) !== null;
    }

    /**
     * Get all **direct children** of this node that match the given tag name (non-recursive).
     *
     * @param string $nodeName The tag/node name to filter.
     * @return DOMElement[] List of matched DOMElement children.
     */
    public function children(string $nodeName): array
    {
        $els = [];
        foreach ($this->node->childNodes as $child) {
            if ($child instanceof DOMElement && $child->nodeName === $nodeName) {
                $els[] = $child;
            }
        }
        return $els;
    }

    /**
     * Get the text content of the **first matching descendant node**.
     *
     * Returns null if not found or if the text is empty.
     *
     * @param string $nodeName Tag/node name to search for.
     * @return string|null Text content or null if not found.
     */
    public function firstChildText(string $nodeName): ?string
    {
        $el = $this->querySelector($nodeName);
        return $el?->textContent ?: null;
    }
    function styleNode()
    {
        return $this->querySelector("w:pStyle");
    }
}
