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
     * @return self
     */
    public function getParent(): self;

    /**
     * @param self $replacement
     * @return DomElementInterface|DomCollectionInterface
     */
    public function replace(self $replacement);
}
