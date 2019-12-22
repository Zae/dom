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
        $this->selectorConverter = $selectorConverter ?? new CssSelectorConverter();
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

        libxml_use_internal_errors(true);
        $this->DOMDocument->loadHTML(
            mb_convert_encoding($string, 'HTML-ENTITIES', 'UTF-8'),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_use_internal_errors(false);

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

        libxml_use_internal_errors(true);
        $this->DOMDocument->loadHTMLFile($path, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_use_internal_errors(false);

        return $this;
    }

    /**
     * @param SimpleXMLElement $element
     *
     * @return DomElement
     * @throws Exception
     */
    protected function loadSimpleXML(SimpleXMLElement $element): self
    {
        $this->sxmlDocument = $element;
        libxml_use_internal_errors(true);
        $dom = dom_import_simplexml($this->sxmlDocument);
        libxml_use_internal_errors(false);

        if (!$dom) {
            throw new Exception('Unable to load XML structure into DOM');
        }

        $this->DOMDocument = $dom;

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
        $selffound = false;
        $prev = [];
        if (!empty($this->DOMDocument->parentNode->childNodes)) {
            foreach ($this->DOMDocument->parentNode->childNodes as $child) {
                if ($child === $this->DOMDocument) {
                    $selffound = true;
                    continue;
                }

                if (!$selffound) {
                    $prev[] = new DomElement($this->selectorConverter, $child);
                }
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
     * @param DomElementInterface $elem
     *
     * @return void
     */
    public function wrap(DomElementInterface $elem): void
    {
        $this->getParent()->dom()->replaceChild($elem->dom(), $this->dom());

        $elem->append($this);
    }

    /**
     * @param DomElementInterface $element
     *
     * @return void
     * @throws Exception
     */
    public function before(DomElementInterface $element): void
    {
        if (empty($this->DOMDocument->parentNode)) {
            throw new Exception('Impossible to put elements before the root');
        }

        $this->DOMDocument->parentNode->insertBefore($element->dom(), $this->DOMDocument);
    }

    /**
     * @param DomElementInterface $element
     *
     * @return void
     * @throws Exception
     */
    public function after(DomElementInterface $element): void
    {
        if (empty($this->DOMDocument->parentNode)) {
            throw new Exception('Impossible to put elements after the root');
        }

        $this->DOMDocument->parentNode->insertBefore($element->dom(), $this->DOMDocument->nextSibling);
    }

    /**
     * @param DomElementInterface $element
     *
     * @return void
     */
    public function append(DomElementInterface $element): void
    {
        $this->DOMDocument->appendChild($element->dom());
    }

    /**
     * @param DomElementInterface|DomCollectionInterface $elements
     *
     * @return void
     */
    public function prepend($elements): void
    {
        if ($elements instanceof DomCollectionInterface) {
            $elements->each(function (DomElementInterface $elem) {
                $this->DOMDocument->insertBefore($elem->dom(), $this->DOMDocument->firstChild);
            });
        } else {
            $this->DOMDocument->insertBefore($elements->dom(), $this->DOMDocument->firstChild);
        }
    }

    /**
     * @return void
     */
    public function empty(): void
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
    }

    /**
     * @return void
     */
    public function remove(): void
    {
        $this->dom()->parentNode->removeChild($this->dom());
    }

    /**
     * @param DomElementInterface $replacement
     */
    public function replace(DomElementInterface $replacement): void
    {
        $this->dom()->parentNode->replaceChild($replacement->dom(), $this->dom());
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
