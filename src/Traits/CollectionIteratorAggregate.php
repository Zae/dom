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
     * Retrieve an external iterator
     *
     * @link  https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->elements->toArray());
    }
}
