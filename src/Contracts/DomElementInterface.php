<?php
declare(strict_types=1);

namespace Zae\DOM\Contracts;

use DOMNode;

/**
 * Interface DomElementInterface
 *
 * @package Zae\DOM
 */
interface DomElementInterface extends DomInterface
{
    /**
     * @return DOMNode
     */
    public function dom(): DOMNode;

    /**
     * @return DomElementInterface
     */
    public function getParent(): self;

    /**
     * @param self $replacement
     */
    public function replace(self $replacement): void;
}
