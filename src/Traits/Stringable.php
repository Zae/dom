<?php

declare(strict_types=1);

namespace Zae\DOM\Traits;

/**
 * Trait Stringable
 *
 * @package Zae\DOM
 */
trait Stringable
{
    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->html();
    }
}
