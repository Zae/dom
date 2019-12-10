<?php
declare(strict_types=1);

namespace Zae\DOM\Contracts;

use Zae\DOM\DomCollection;

/**
 * Interface DomCollectionInterface
 *
 * @package Zae\DOM
 */
interface DomCollectionInterface extends DomInterface
{
    /**
     * @param DomCollectionInterface $collection
     *
     * @return DomCollection
     */
    public function merge(self $collection): DomCollection;

    /**
     * @param callable $callback
     *
     * @return DomCollection
     */
    public function map(callable $callback): DomCollection;

    /**
     * @param callable $callback
     */
    public function each(callable $callback): void;

    /**
     * @return DomInterface
     */
    public function first(): DomInterface;
}
