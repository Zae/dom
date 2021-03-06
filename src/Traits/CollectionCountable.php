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
     * Count elements of an object
     *
     * @link  https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count(): int
    {
        return $this->elements->count();
    }
}
