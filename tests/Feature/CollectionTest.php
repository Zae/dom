<?php
declare(strict_types=1);

namespace Zae\DOM\Tests\Feature;

use Zae\DOM\DomElement;
use Zae\DOM\Tests\TestCase;

/**
 * Class CollectionTest
 *
 * @package Zae\DOM\Tests\Feature
 */
class CollectionTest extends TestCase
{
    const html3 = '<div class="parent"><div class="firstchild"></div><div class="lastchild"></div></div>';

    /**
     * @test
     * @group collection
     */
    public function it_can_find_next_siblings_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString(static::html3);

        $first = $doc->find('.firstchild');
        $last = $first->nextSiblings();

        $this->assertStringContainsString('<div class="lastchild"></div>', (string)$last);
    }

    /**
     * @test
     * @group collection
     */
    public function it_can_wrap_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString(static::html3);

        $first = $doc->find('.firstchild');
        $last = $doc->find('.lastchild')->first();

        $first->wrap($last);

        $this->assertStringContainsString('<div class="parent"><div class="lastchild"><div class="firstchild"></div></div></div>', (string)$doc);
    }

    /**
     * @test
     * @group collection
     */
    public function it_can_before_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString(static::html3);

        $first = $doc->find('.firstchild');
        $last = $doc->find('.lastchild')->first();

        $first->before($last);

        $this->assertStringContainsString('<div class="parent"><div class="lastchild"></div><div class="firstchild"></div></div>', (string)$doc);
    }

    /**
     * @test
     * @group collection
     */
    public function it_can_after_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString(static::html3);

        $first = $doc->find('.firstchild')->first();
        $last = $doc->find('.lastchild');

        $last->after($first);

        $this->assertStringContainsString('<div class="parent"><div class="lastchild"></div><div class="firstchild"></div></div>', (string)$doc);
    }

    /**
     * @test
     * @group collection
     */
    public function it_can_append_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString(static::html3);

        $first = $doc->find('.firstchild');
        $last = $doc->find('.lastchild')->first();

        $first->append($last);

        $this->assertStringContainsString('<div class="parent"><div class="firstchild"><div class="lastchild"></div></div></div>', (string)$doc);
    }

    /**
     * @test
     * @group collection
     */
    public function it_can_prepend_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString(static::html3);

        $first = $doc->find('.firstchild');
        $last = $doc->find('.lastchild')->first();

        $first->prepend($last);

        $this->assertStringContainsString('<div class="parent"><div class="firstchild"><div class="lastchild"></div></div></div>', (string)$doc);
    }

    /**
     * @test
     * @group collection
     */
    public function it_can_empty_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString(static::html3);

        $parent = $doc->find('.parent');

        $parent->empty();

        $this->assertStringContainsString('<div class="parent"></div>', (string)$doc);
    }
}
