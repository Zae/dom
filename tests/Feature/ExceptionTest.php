<?php

declare(strict_types=1);

namespace Zae\DOM\Tests\Feature;

use Zae\DOM\DomElement;
use Zae\DOM\Tests\TestCase;

/**
 * Class ExceptionTest
 *
 * @package Zae\DOM\Tests\Feature
 */
class ExceptionTest extends TestCase
{
    /**
     * @test
     * @group exception
     */
    public function it_throws_an_error_on_empty_input(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Empty string supplied as input');

        $doc = new DomElement();
        $doc->loadString('');
    }
}
