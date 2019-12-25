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

    const html3 = '<div class="parent"><div class="firstchild"></div><div class="lastchild"></div></div>';

    /**
     * @test
     * @group modify
     */
    public function it_can_use_after(): void
    {
        $string = $this->fixEmWrappedFigures(static::$html2);

        $this->assertStringContainsString('<em></em><figure></figure>', $string);
    }

    /**
     * @test
     * @group modify
     */
    public function it_can_use_prepend(): void
    {
        $doc = new DomElement();
        $doc->loadString(static::html3);

        $parent = $doc->find('.parent');
        $last = $doc->find('.lastchild');

        $parent->prepend($last);

        $this->assertStringContainsString('<div class="parent"><div class="lastchild"></div><div class="firstchild"></div></div>', (string)$doc);
    }

    /**
     * @test
     * @group modify
     */
    public function it_can_use_prepend_two(): void
    {
        $doc = new DomElement();
        $doc->loadString(static::html3);

        $last = $doc->find('.lastchild');

        $doc->prepend($last);

        $this->assertStringContainsString('<div class="lastchild"></div><div class="parent"><div class="firstchild"></div></div>', (string)$doc);
    }

    /**
     * @test
     * @group modify
     */
    public function it_can_find_preceding(): void
    {
        $doc = new DomElement();
        $doc->loadString(static::html3);

        $last = $doc->find('.lastchild');

        $preceding = $last->precedingSiblings();

        $this->assertStringContainsString('<div class="firstchild"></div>', (string)$preceding);
    }

    /**
     * @test
     */
    public function it_can_use_before(): void
    {
        $doc = new DomElement();
        $doc->loadString(static::html3);

        $first = $doc->find('.firstchild')->first();
        $last = $doc->find('.lastchild')->first();

        $first->before($last);

        $this->assertStringContainsString('<div class="parent"><div class="lastchild"></div><div class="firstchild"></div></div>', (string)$doc);
    }

    /**
     * @test
     * @group modify
     */
    public function it_can_empty(): void
    {
        $doc = new DomElement();
        $doc->loadString(static::html3);

        $first = $doc->find('.parent')->first();

        $first->empty();

        $this->assertStringContainsString('<div class="parent"></div>', (string)$doc);
    }

    /**
     * @test
     * @group modify
     */
    public function it_can_replace(): void
    {
        $doc = new DomElement();
        $doc->loadString(static::html3);

        $first = $doc->find('.firstchild')->first();
        $last = $doc->find('.lastchild')->first();

        $first->replace($last);

        $this->assertStringContainsString('<div class="parent"><div class="lastchild"></div></div>', (string)$doc);
    }

    /**
     * @test
     * @group modify
     */
    public function it_no_breaks(): void
    {
        $doc = new DomElement(null, new \DOMText());

        $this->assertEquals('', (string)$doc);
    }

    /**
     * @test
     * @group modify
     */
    public function it_can_put_elements_before_root(): void
    {
        $doc = new DomElement();
        $doc->loadString(static::html3);

        $parent = $doc->find('.parent');
        $first = $doc->find('.firstchild')->first();
        $parent->before($first);

        $this->assertEquals("<div class=\"firstchild\"></div><div class=\"parent\"><div class=\"lastchild\"></div></div>\n", (string)$doc);
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
