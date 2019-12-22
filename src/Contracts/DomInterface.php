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
     * @return void
     */
    public function empty(): void;

    /**
     * @return void
     */
    public function remove(): void;

    /**
     * @return string
     */
    public function html(): string;

    /**
     * @param DomElementInterface $elem
     *
     * @return void
     */
    public function wrap(DomElementInterface $elem): void;

    /**
     * @param DomElementInterface $element
     *
     * @return void
     */
    public function before(DomElementInterface $element): void;

    /**
     * @param DomElementInterface $element
     */
    public function after(DomElementInterface $element): void;

    /**
     * @param DomElementInterface $element
     *
     * @return void
     */
    public function append(DomElementInterface $element): void;

    /**
     * @param DomElementInterface|DomCollectionInterface $element
     */
    public function prepend($element): void;
}
