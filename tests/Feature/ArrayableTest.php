<?php
declare(strict_types=1);

namespace Zae\DOM\Tests\Feature;

use Zae\DOM\DomElement;
use Zae\DOM\Tests\TestCase;

/**
 * Class ArrayableTest
 *
 * @package Zae\DOM\Tests\Feature
 */
class ArrayableTest extends TestCase
{
    /**
     * @test
     * @group array
     */
    public function it_can_only_set_valid_as_array(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You can only insert DomElementInterface elements');

        $doc = new DomElement();
        $doc->loadString('<div class="parent"><div class="firstchild first"></div><div class="firstchild second"></div></div>');

        $first = $doc->find('.firstchild');

        $first[1] = 'NOTALLOWED';
    }

    /**
     * @test
     * @group array
     */
    public function it_acts_like_an_array(): void
    {
        $doc = new DomElement();
        $doc->loadString('<div class="parent"><div class="firstchild first"></div><div class="firstchild second"></div></div>');

        $first = $doc->find('.firstchild');

        $this->assertCount(2, $first);
        $this->assertIsIterable($first);

        $this->assertTrue(isset($first[0]));
        $this->assertTrue(isset($first[1]));
        $this->assertFalse(isset($first[2]));

        $this->assertEquals("<div class=\"firstchild first\"></div>\n", (string)$first[0]);
        $this->assertEquals("<div class=\"firstchild second\"></div>\n", (string)$first[1]);

        $tmp = $first[0];
        $first[0] = $first[1];
        $first[1] = $tmp;

        $this->assertEquals("<div class=\"firstchild first\"></div>\n", (string)$first[1]);
        $this->assertEquals("<div class=\"firstchild second\"></div>\n", (string)$first[0]);

        unset($first[0]);
        $this->assertFalse(isset($first[0]));
        $this->assertCount(1, $first);

        $this->assertEquals("<div class=\"parent\"><div class=\"firstchild first\"></div></div>\n", (string)$doc);
    }

    /**
     * @test
     * @group array
     */
    public function it_can_iterate_over_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString('<div class="parent"><div class="firstchild first"></div><div class="firstchild second"></div></div>');

        $first = $doc->find('.firstchild');

        $i = 0;
        $expected = ["<div class=\"firstchild first\"></div>\n", "<div class=\"firstchild second\"></div>\n"];
        foreach ($first as $node) {
            $this->assertEquals($expected[$i++], (string)$node);
        }

        $this->assertEquals(2, $i);
        $this->assertIsIterable($first);
        $this->assertCount(2, $first);
    }
}
