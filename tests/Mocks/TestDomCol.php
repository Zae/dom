<?php

namespace Zae\DOM\Tests\Mocks;

use Illuminate\Support\Collection;
use Zae\DOM\Contracts\DomCollectionInterface;
use Zae\DOM\Contracts\DomElementInterface;
use Zae\DOM\Contracts\DomInterface;
use Zae\DOM\DomCollection;

class TestDomCol implements DomCollectionInterface
{
    /**
     * @inheritDoc
     */
    public function merge(DomCollectionInterface $collection): DomCollection
    {
        // TODO: Implement merge() method.
    }

    /**
     * @inheritDoc
     */
    public function map(callable $callback): DomCollection
    {
        // TODO: Implement map() method.
    }

    /**
     * @inheritDoc
     */
    public function each(callable $callback)
    {
        // TODO: Implement each() method.
    }

    /**
     * @inheritDoc
     */
    public function first(): ?DomInterface
    {
        // TODO: Implement first() method.
    }

    /**
     * @inheritDoc
     */
    public function elements(): Collection
    {
        // TODO: Implement elements() method.
    }

    /**
     * @inheritDoc
     */
    public function find(string $selector): DomCollection
    {
        // TODO: Implement find() method.
    }

    /**
     * @inheritDoc
     */
    public function findxPath(string $selector): DomCollection
    {
        // TODO: Implement findxPath() method.
    }

    /**
     * @inheritDoc
     */
    public function text(): string
    {
        // TODO: Implement text() method.
    }

    /**
     * @inheritDoc
     */
    public function precedingSiblings(): DomCollection
    {
        // TODO: Implement precedingSiblings() method.
    }

    /**
     * @inheritDoc
     */
    public function nextSiblings(): DomCollection
    {
        // TODO: Implement nextSiblings() method.
    }

    /**
     * @inheritDoc
     */
    public function empty()
    {
        // TODO: Implement empty() method.
    }

    /**
     * @inheritDoc
     */
    public function remove()
    {
        // TODO: Implement remove() method.
    }

    /**
     * @inheritDoc
     */
    public function html(): string
    {
        // TODO: Implement html() method.
    }

    /**
     * @inheritDoc
     */
    public function wrap(DomElementInterface $element)
    {
        // TODO: Implement wrap() method.
    }

    /**
     * @inheritDoc
     */
    public function before(DomElementInterface $element)
    {
        // TODO: Implement before() method.
    }

    /**
     * @inheritDoc
     */
    public function after(DomElementInterface $element)
    {
        // TODO: Implement after() method.
    }

    /**
     * @inheritDoc
     */
    public function append(DomElementInterface $element)
    {
        // TODO: Implement append() method.
    }

    /**
     * @inheritDoc
     */
    public function prepend($element)
    {
        // TODO: Implement prepend() method.
    }

    /**
     * @inheritDoc
     */
    public function attr(string $name, $value = null)
    {
        // TODO: Implement attr() method.
    }
}
