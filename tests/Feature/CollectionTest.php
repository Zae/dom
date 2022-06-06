<?php

declare(strict_types=1);

namespace Zae\DOM\Tests\Feature;

use Zae\DOM\DomElement;
use Zae\DOM\Tests\Mocks\TestDomCol;
use Zae\DOM\Tests\TestCase;
use Zae\DOM\Tests\Traits\useMocks;

/**
 * Class CollectionTest
 *
 * @package Zae\DOM\Tests\Feature
 */
class CollectionTest extends TestCase
{
    use useMocks;

    private const html3 = '<div class="parent"><div class="firstchild"></div><div class="lastchild"></div></div>';

    /**
     * @test
     * @group collection
     */
    public function it_can_find_next_siblings_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString(self::html3);

        $first = $doc->find('.firstchild');
        $last = $first->nextSiblings();

        static::assertStringContainsString('<div class="lastchild"></div>', (string)$last);
    }

    /**
     * @test
     * @group collection
     */
    public function it_can_wrap_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString(self::html3);

        $first = $doc->find('.firstchild');
        $last = $doc->find('.lastchild')->first();

        $first->wrap($last);

        static::assertStringContainsString('<div class="parent"><div class="lastchild"><div class="firstchild"></div></div></div>', (string)$doc);
    }

    /**
     * @test
     * @group collection
     */
    public function it_can_before_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString(self::html3);

        $first = $doc->find('.firstchild');
        $last = $doc->find('.lastchild')->first();

        $first->before($last);

        static::assertStringContainsString('<div class="parent"><div class="lastchild"></div><div class="firstchild"></div></div>', (string)$doc);
    }

    /**
     * @test
     * @group collection
     */
    public function it_can_after_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString(self::html3);

        $first = $doc->find('.firstchild')->first();
        $last = $doc->find('.lastchild');

        $last->after($first);

        static::assertStringContainsString('<div class="parent"><div class="lastchild"></div><div class="firstchild"></div></div>', (string)$doc);
    }

    /**
     * @test
     * @group collection
     */
    public function it_can_append_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString(self::html3);

        $first = $doc->find('.firstchild');
        $last = $doc->find('.lastchild')->first();

        $first->append($last);

        static::assertStringContainsString('<div class="parent"><div class="firstchild"><div class="lastchild"></div></div></div>', (string)$doc);
    }

    /**
     * @test
     * @group collection
     */
    public function it_can_prepend_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString(self::html3);

        $first = $doc->find('.firstchild');
        $last = $doc->find('.lastchild')->first();

        $first->prepend($last);

        static::assertStringContainsString('<div class="parent"><div class="firstchild"><div class="lastchild"></div></div></div>', (string)$doc);
    }

    /**
     * @test
     * @group collection
     */
    public function it_can_empty_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString(self::html3);

        $parent = $doc->find('.parent');

        $parent->empty();

        static::assertStringContainsString('<div class="parent"></div>', (string)$doc);
    }

    /**
     * @test
     * @group collection
     *
     * @return void
     * @throws \Exception
     */
    public function it_should_call_each_when_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString(self::html3);

        $collectionMock = \Mockery::mock(TestDomCol::class);
        $collectionMock
            ->expects()
            ->each(\Mockery::type('callable'));

        $doc->prepend($collectionMock);
    }

    /**
     * @test
     * @group collection
     *
     * @return void
     * @throws \Exception
     */
    public function it_should_call_each_when_collection_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString(self::html3);

        $collectionMock = \Mockery::mock(TestDomCol::class);
        $collectionMock
            ->expects()
            ->each(\Mockery::type('callable'));

        $doc->find('.parent > *')->prepend($collectionMock);
    }
}
