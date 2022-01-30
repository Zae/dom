<?php

declare(strict_types=1);

namespace Zae\DOM;

use Closure;
use DOMDocument;
use DOMNode;
use Exception;
use SimpleXMLElement;
use Symfony\Component\CssSelector\CssSelectorConverter;
use Zae\DOM\Contracts\DomCollectionInterface;
use Zae\DOM\Contracts\DomElementInterface;
use Zae\DOM\Traits\Stringable;

/**
 * Class DomElement
 *
 * @package Zae\DOM
 * @psalm-suppress PropertyNotSetInConstructor
 */
class DomElement implements DomElementInterface
{
    use Stringable;

    /**
     * @var CssSelectorConverter
     */
    private $selectorConverter;

    /**
     * @var DOMDocument|\DOMElement|DOMNode
     */
    private $DOMDocument;

    /**
     * @var SimpleXMLElement
     */
    private $sxmlDocument;

    /**
     * DomElement constructor.
     *
     * @param CssSelectorConverter|null $selectorConverter
     * @param DOMNode|null              $DOMDocument
     */
    public function __construct(
        ?CssSelectorConverter $selectorConverter = null,
        ?DOMNode $DOMDocument = null
    ) {
        $this->selectorConverter = $selectorConverter ?? new CssSelectorConverter(true);
        $this->DOMDocument = $DOMDocument ?? new DOMDocument();
    }

    /**
     * @param string $string
     *
     * @return DomElement
     * @throws Exception
     */
    public function loadString(string $string): self
    {
        if (!$this->DOMDocument instanceof DOMDocument) {
            throw new Exception('You can only loadString on a root instance.');
        }

        if (empty($string)) {
            throw new Exception('Empty string supplied as input');
        }

        $use_errors = libxml_use_internal_errors(true);
        /** @psalm-suppress ArgumentTypeCoercion */
        $this->DOMDocument->loadHTML(
            mb_convert_encoding($string, 'HTML-ENTITIES', 'UTF-8'),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_use_internal_errors($use_errors);

        return $this;
    }

    /**
     * @param string $path
     *
     * @return DomElement
     * @throws Exception
     */
    public function loadHTML(string $path): self
    {
        if (!$this->DOMDocument instanceof DOMDocument) {
            throw new Exception('You can only loadHTML on a root instance.');
        }

        $use_errors = libxml_use_internal_errors(true);
        $this->DOMDocument->loadHTMLFile($path, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_use_internal_errors($use_errors);

        return $this;
    }

    /**
     * @param string $selector
     *
     * @return DomCollection
     */
    public function find(string $selector): DomCollection
    {
        return $this->findxPath(
            $this->selectorConverter->toXPath($selector)
        );
    }

    /**
     * @param string $selector
     *
     * @return DomCollection
     */
    public function findxPath(string $selector): DomCollection
    {
        return $this->xPathToCollection(
            $selector
        );
    }

    /**
     * @return DOMNode
     */
    public function dom(): DOMNode
    {
        return $this->DOMDocument;
    }

    /**
     * @return string
     */
    public function text(): string
    {
        return $this->DOMDocument->textContent;
    }

    /**
     * @return DomCollection
     */
    public function precedingSiblings(): DomCollection
    {
        $prev = [];
        if (!empty($this->DOMDocument->parentNode->childNodes)) {
            foreach ($this->DOMDocument->parentNode->childNodes as $child) {
                if ($child === $this->DOMDocument) {
                    break;
                }

                $prev[] = new DomElement($this->selectorConverter, $child);
            }
        }

        return new DomCollection($prev, $this->selectorConverter);
    }

    /**
     * @return DomCollection
     */
    public function nextSiblings(): DomCollection
    {
        $selffound = false;
        $next = [];

        if (!empty($this->DOMDocument->parentNode->childNodes)) {
            foreach ($this->DOMDocument->parentNode->childNodes as $child) {
                if ($child === $this->DOMDocument) {
                    $selffound = true;
                    continue;
                }

                if ($selffound) {
                    $next[] = new DomElement($this->selectorConverter, $child);
                }
            }
        }

        return new DomCollection($next, $this->selectorConverter);
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return DomElement
     * @throws Exception
     */
    public function create(string $name, ?string $value = null): self
    {
        if (!$this->DOMDocument instanceof DOMDocument) {
            throw new Exception('You can only call create on a root instance');
        }

        if ($value === null) {
            $elem = $this->DOMDocument->createElement($name);
        } else {
            $elem = $this->DOMDocument->createElement($name, $value);
        }

        return new static($this->selectorConverter, $elem);
    }

    /**
     * @param DomElementInterface $element
     *
     * @return self
     */
    public function wrap(DomElementInterface $element): self
    {
        $this->getParent()->dom()->replaceChild($element->dom(), $this->dom());

        $element->append($this);

        return $this;
    }

    /**
     * @param DomElementInterface $element
     *
     * @return self
     * @throws Exception
     */
    public function before(DomElementInterface $element): self
    {
        if (empty($this->DOMDocument->parentNode)) {
            throw new Exception('Impossible to put elements before the root');
        }

        $this->DOMDocument->parentNode->insertBefore($element->dom(), $this->DOMDocument);

        return $this;
    }

    /**
     * @param DomElementInterface $element
     *
     * @return self
     * @throws Exception
     */
    public function after(DomElementInterface $element): self
    {
        if (empty($this->DOMDocument->parentNode)) {
            throw new Exception('Impossible to put elements after the root');
        }

        $this->DOMDocument->parentNode->insertBefore($element->dom(), $this->DOMDocument->nextSibling);

        return $this;
    }

    /**
     * @param DomElementInterface $element
     *
     * @return self
     */
    public function append(DomElementInterface $element): self
    {
        $this->DOMDocument->appendChild($element->dom());

        return $this;
    }

    /**
     * @param DomElementInterface|DomCollectionInterface $element
     *
     * @return self
     */
    public function prepend($element): self
    {
        if ($element instanceof DomCollectionInterface) {
            $element->each(function (DomElementInterface $elem) {
                $this->DOMDocument->insertBefore($elem->dom(), $this->DOMDocument->firstChild);
            });
        } else {
            $this->DOMDocument->insertBefore($element->dom(), $this->DOMDocument->firstChild);
        }

        return $this;
    }

    /**
     * @return self
     */
    public function empty(): self
    {
        /*
         * First we copy the nodes from the nodeCollection to an array,
         * so we can removeChild() them later without messing with our
         * loop.
         */
        $nodes = [];
        foreach ($this->dom()->childNodes as $node) {
            $nodes[] = $node;
        }

        foreach ($nodes as $node) {
            $this->dom()->removeChild($node);
        }

        return $this;
    }

    /**
     * @return self
     */
    public function remove(): self
    {
        $parentNode = $this->dom()->parentNode;
        if ($parentNode) {
            $parentNode->removeChild($this->dom());
        }

        return $this;
    }

    /**
     * @param DomElementInterface $replacement
     *
     * @return self
     */
    public function replace(DomElementInterface $replacement): self
    {
        $parentNode = $this->dom()->parentNode;

        if ($parentNode) {
            $parentNode->replaceChild($replacement->dom(), $this->dom());
        }
        return $this;
    }

    /**
     * @return DomElementInterface
     */
    public function getParent(): DomElementInterface
    {
        return new static($this->selectorConverter, $this->dom()->parentNode);
    }

    /**
     * @return string
     */
    public function html(): string
    {
        if ($this->DOMDocument instanceof DOMDocument) {
            return $this->DOMDocument->saveHTML();
        }

        if ($this->DOMDocument instanceof \DOMElement) {
            $doc = new DOMDocument();
            $node = $doc->importNode($this->DOMDocument, true);

            if ($node) {
                $doc->appendChild($node);
            }

            return $doc->saveHTML();
        }

        return '';
    }

    /**
     * @param string $name
     * @param null $value
     *
     * @return $this|string
     * @throws Exception
     */
    public function attr(string $name, $value = null)
    {
        if (!($this->DOMDocument instanceof \DOMElement)) {
            throw new Exception('This element does not support attributes');
        }

        if ($value === null) {
            return $this->DOMDocument->getAttribute($name);
        }

        $this->DOMDocument->setAttribute($name, $value);

        return $this;
    }

    /**
     * @param string $xpath
     *
     * @return DomCollection
     */
    private function xPathToCollection(string $xpath): DomCollection
    {
        $this->reloadSimpleXMLStruct();

        $collection = collect($this->sxmlDocument->xpath($xpath))
            ->map(Closure::fromCallable([$this, 'convertSimpleXmlToDomElement']))
            ->toArray();

        return new DomCollection($collection, $this->selectorConverter);
    }

    /**
     * @param SimpleXMLElement $element
     *
     * @return DomElement
     * @throws Exception
     */
    private function loadSimpleXML(SimpleXMLElement $element): self
    {
        $this->sxmlDocument = $element;
        $use_errors = libxml_use_internal_errors(true);
        $dom = dom_import_simplexml($this->sxmlDocument);
        libxml_use_internal_errors($use_errors);

        if (!$dom) {
            throw new Exception('Unable to load XML structure into DOM');
        }

        $this->DOMDocument = $dom;

        return $this;
    }

    /**
     * @param SimpleXMLElement $element
     *
     * @return DomElement
     * @throws Exception
     */
    private function convertSimpleXmlToDomElement(SimpleXMLElement $element): DomElement
    {
        $domElement = new static($this->selectorConverter);
        $domElement->loadSimpleXML($element);

        return $domElement;
    }

    private function reloadSimpleXMLStruct(): void
    {
        if ($this->DOMDocument instanceof DOMDocument || $this->DOMDocument instanceof \DOMElement) {
            $this->sxmlDocument = simplexml_import_dom($this->DOMDocument);
        }
    }
}
