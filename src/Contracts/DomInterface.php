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
     * @return mixed
     */
    public function empty();

    /**
     * @return mixed
     */
    public function remove();

    /**
     * @return string
     */
    public function html(): string;

    /**
     * @param DomElementInterface $elem
     *
     * @return mixed
     */
    public function wrap(DomElementInterface $elem);

    /**
     * @param DomElementInterface $element
     *
     * @return mixed
     */
    public function before(DomElementInterface $element);

    /**
     * @param DomElementInterface $element
     */
    public function after(DomElementInterface $element): void;

    /**
     * @param DomElementInterface $element
     *
     * @return mixed
     */
    public function append(DomElementInterface $element);

    /**
     * @param DomInterface $element
     */
    public function prepend(DomInterface $element): void;
}
