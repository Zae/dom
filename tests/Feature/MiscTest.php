<?php
declare(strict_types=1);

namespace Zae\DOM\Tests\Feature;

use Zae\DOM\DomElement;
use Zae\DOM\Tests\TestCase;

/**
 * Class DomModificationTest
 *
 * @package Zae\DOM\Tests\Feature
 */
class MiscTest extends TestCase
{
    const html3 = '<div class="parent"><div class="firstchild"></div><div class="lastchild"></div></div>';

    /**
     * @test
     * @group modify
     */
    public function it_can_only_load_on_root(): void
    {
        $this->expectExceptionMessage('You can only loadString on a root instance.');
        $this->expectException(\Exception::class);

        $doc = new DomElement();

        $a = $doc->create('a');
        $a->loadString('ASD');
    }

    /**
     * @test
     * @group modify
     */
    public function it_can_chain(): void
    {
        $doc = new DomElement();
        $doc->loadString(static::html3);

        $first = $doc->find('.firstchild')->first();
        $last = $doc->find('.lastchild')->first();
        $doc->find('.parent')
            ->wrap($first)
            ->wrap($last);

        $this->assertEquals("<div class=\"firstchild\"><div class=\"lastchild\"><div class=\"parent\"></div></div></div>\n", (string)$doc);
    }
}
