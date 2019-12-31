<?php
declare(strict_types=1);

namespace Zae\DOM\Contracts;

use Zae\DOM\DomCollection;

/**
 * Interface DomInterface
 *
 * @package Zae\DOM
 */
interface DomInterface
{
    /**
     * @param string $selector
     *
     * @return DomCollection
     */
    public function find(string $selector): DomCollection;

    /**
     * @param string $selector
     *
     * @return DomCollection
     */
    public function findxPath(string $selector): DomCollection;

    /**
     * @return string
     */
    public function text(): string;

    /**
     * @return DomCollection
     */
    public function precedingSiblings(): DomCollection;

    /**
     * @return DomCollection
     */
    public function nextSiblings(): DomCollection;

    /**
     * @return DomElementInterface|DomCollectionInterface
     */
    public function empty();

    /**
     * @return DomElementInterface|DomCollectionInterface
     */
    public function remove();

    /**
     * @return string
     */
    public function html(): string;

    /**
     * @param DomElementInterface $elem
     *
     * @return DomElementInterface|DomCollectionInterface
     */
    public function wrap(DomElementInterface $elem);

    /**
     * @param DomElementInterface $element
     *
     * @return DomElementInterface|DomCollectionInterface
     */
    public function before(DomElementInterface $element);

    /**
     * @param DomElementInterface $element
     *
     * @return DomElementInterface|DomCollectionInterface
     */
    public function after(DomElementInterface $element);

    /**
     * @param DomElementInterface $element
     *
     * @return DomElementInterface|DomCollectionInterface
     */
    public function append(DomElementInterface $element);

    /**
     * @param DomElementInterface|DomCollectionInterface $element
     *
     * @return DomElementInterface|DomCollectionInterface
     */
    public function prepend($element);

    /**
     * @param string $name
     * @param null   $value
     *
     * @return DomElementInterface|DomCollectionInterface|string
     */
    public function attr(string $name, $value = null);
}
