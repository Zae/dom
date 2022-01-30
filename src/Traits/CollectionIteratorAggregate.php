<?php

declare(strict_types=1);

namespace Zae\DOM\Traits;

use ArrayIterator;
use Traversable;

/**
 * Trait CollectionIteratorAgggregate
 *
 * @package Zae\DOM
 */
trait CollectionIteratorAggregate
{
    /**
     * Get an iterator over the elements variable in this class.
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->elements->toArray());
    }
}
