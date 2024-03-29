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
class DomModificationTest extends TestCase
{
    private static $html2 = <<<'HTML'
<html>
    <body>
        <em><figure></figure></em>
        <em><figure></figure></em>
    </body>
</html>

HTML;

    private const html3 = '<div class="parent"><div class="firstchild"></div><div class="lastchild"></div></div>';
    private const html4 = '<div class="parent"><div class="firstchild"></div><div class="middlechild"></div><div class="lastchild"></div></div>';

    /**
     * @test
     * @group modify
     */
    public function it_can_use_after(): void
    {
        $string = $this->fixEmWrappedFigures(self::$html2);

        static::assertStringContainsString('<em></em><figure></figure>', $string);
    }

    /**
     * @test
     * @group modify
     */
    public function it_can_use_prepend(): void
    {
        $doc = new DomElement();
        $doc->loadString(self::html3);

        $parent = $doc->find('.parent');
        $last = $doc->find('.lastchild');

        $parent->prepend($last);

        static::assertStringContainsString('<div class="parent"><div class="lastchild"></div><div class="firstchild"></div></div>', (string)$doc);
    }

    /**
     * @test
     * @group modify
     */
    public function it_can_use_prepend_two(): void
    {
        $doc = new DomElement();
        $doc->loadString(self::html3);

        $last = $doc->find('.lastchild');

        $doc->prepend($last);

        static::assertStringContainsString('<div class="lastchild"></div><div class="parent"><div class="firstchild"></div></div>', (string)$doc);
    }

    /**
     * @test
     * @group modify
     */
    public function it_can_find_preceding(): void
    {
        $doc = new DomElement();
        $doc->loadString(static::html4);

        $last = $doc->find('.middlechild');

        $preceding = $last->precedingSiblings();

        static::assertEquals('<div class="firstchild"></div>' . PHP_EOL, (string)$preceding);
    }

    /**
     * @test
     * @group modify
     */
    public function it_can_find_next(): void
    {
        $doc = new DomElement();
        $doc->loadString(static::html4);

        $last = $doc->find('.middlechild');

        $preceding = $last->nextSiblings();

        static::assertEquals('<div class="lastchild"></div>' . PHP_EOL, (string)$preceding);
    }

    /**
     * @test
     */
    public function it_can_use_before(): void
    {
        $doc = new DomElement();
        $doc->loadString(self::html3);

        $first = $doc->find('.firstchild')->first();
        $last = $doc->find('.lastchild')->first();

        $first->before($last);

        static::assertStringContainsString('<div class="parent"><div class="lastchild"></div><div class="firstchild"></div></div>', (string)$doc);
    }

    /**
     * @test
     * @group modify
     */
    public function it_can_empty(): void
    {
        $doc = new DomElement();
        $doc->loadString(self::html3);

        $first = $doc->find('.parent')->first();

        $first->empty();

        static::assertStringContainsString('<div class="parent"></div>', (string)$doc);
    }

    /**
     * @test
     * @group modify
     */
    public function it_can_replace(): void
    {
        $doc = new DomElement();
        $doc->loadString(self::html3);

        $first = $doc->find('.firstchild')->first();
        $last = $doc->find('.lastchild')->first();

        $first->replace($last);

        static::assertStringContainsString('<div class="parent"><div class="lastchild"></div></div>', (string)$doc);
    }

    /**
     * @test
     * @group modify
     */
    public function it_no_breaks(): void
    {
        $doc = new DomElement(null, new \DOMText());

        static::assertEquals('', (string)$doc);
    }

    /**
     * @test
     * @group modify
     */
    public function it_can_put_elements_before_root(): void
    {
        $doc = new DomElement();
        $doc->loadString(self::html3);

        $parent = $doc->find('.parent');
        $first = $doc->find('.firstchild')->first();
        $parent->before($first);

        static::assertEquals("<div class=\"firstchild\"></div><div class=\"parent\"><div class=\"lastchild\"></div></div>\n", (string)$doc);
    }

    /**
     * @test
     * @group modify
     */
    public function it_can_remove_elements(): void
    {
        $doc = new DomElement();
        $doc->loadString(self::html3);

        $doc->find('.firstchild')->remove();

        static::assertEquals("<div class=\"parent\"><div class=\"lastchild\"></div></div>\n", (string)$doc);
    }

    /**
     * Find any <figure>'s that are wrapped in <em>'s and move them to be a sibling of the <em>, this way the
     * HTMLPurifier won't try to fix the nesting of the <em>.
     *
     * @param string $html
     *
     * @return string
     */
    private function fixEmWrappedFigures(string $html): string
    {
        $doc = new DomElement();
        $doc->loadString($html);

        $doc->find('em > figure')
            ->map(static function (DomElement $figure) {
                $em = $figure->getParent();

                $em->after($figure);
            });

        return (string)$doc;
    }
}
