<?php

declare(strict_types=1);

namespace Zae\DOM\Traits;

/**
 * Trait CollectionCountable
 *
 * @package Zae\DOM
 */
trait CollectionCountable
{
    /**
     * Counts the amount of element in the variable elements.
     */
    public function count(): int
    {
        return $this->elements->count();
    }
}
