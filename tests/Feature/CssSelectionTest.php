<?php
declare(strict_types=1);

namespace Zae\DOM\Tests\Feature;

use Zae\DOM\DomElement;
use Zae\DOM\Tests\TestCase;

/**
 * Class CssSelectionTest
 *
 * @package Zae\DOM\Tests\Feature
 */
class CssSelectionTest extends TestCase
{
    public static $html = <<<'HTML'
<html>
    <body>
        <div class="caption">
            <img> CAPTION 1
        </div>
        <div class="caption">
            <img> CAPTION 2
        </div>
    </body>
</html>

HTML;

    public static $html2 = <<<'HTML'
<html>
    <body>
        <em><figure></figure></em>
        <em><figure></figure></em>
    </body>
</html>

HTML;

    /**
     * @test
     */
    public function it_can_use_basic_css_selector(): void
    {
        $doc = new DomElement();
        $doc->loadString(static::$html);

        $captions = $doc->find('.caption');

        $this->assertCount(2, $captions);
        $this->assertStringContainsString('<div class="caption">', (string)$captions);
    }

    /**
     * @test
     */
    public function it_can_use_nested_css_selector(): void
    {
        $string = $this->wrapCaptionableImages(static::$html);

        $this->assertStringContainsString('<figcaption><h1>CAPTION 1</h1></figcaption>', $string);
        $this->assertStringContainsString('<figcaption><h1>CAPTION 2</h1></figcaption>', $string);
    }

    /**
     * @test
     */
    public function it_can_use_after(): void
    {
        $string = $this->fixEmWrappedFigures(static::$html2);

        $this->assertStringContainsString('<em></em><figure></figure>', $string);
    }

    /**
     * @test
     */
    public function it_can_use_prepend(): void
    {
        $doc = new DomElement();
        $doc->loadString('<div class="parent"><div class="firstchild"></div><div class="lastchild"></div></div>');

        $parent = $doc->find('.parent');
        $last = $doc->find('.lastchild');

        $parent->prepend($last);

        $this->assertStringContainsString('<div class="parent"><div class="lastchild"></div><div class="firstchild"></div></div>', (string)$doc);
    }

    /**
     * @test
     */
    public function it_can_use_prepend_two(): void
    {
        $doc = new DomElement();
        $doc->loadString('<div class="parent"><div class="firstchild"></div><div class="lastchild"></div></div>');

        $last = $doc->find('.lastchild');

        $doc->prepend($last);

        $this->assertStringContainsString('<div class="lastchild"></div><div class="parent"><div class="firstchild"></div></div>', (string)$doc);
    }

    /**
     * @test
     */
    public function it_can_find_preceding(): void
    {
        $doc = new DomElement();
        $doc->loadString('<div class="parent"><div class="firstchild"></div><div class="lastchild"></div></div>');

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
        $doc->loadString('<div class="parent"><div class="firstchild"></div><div class="lastchild"></div></div>');

        $first = $doc->find('.firstchild')->first();
        $last = $doc->find('.lastchild')->first();

        $first->before($last);

        $this->assertStringContainsString('<div class="parent"><div class="lastchild"></div><div class="firstchild"></div></div>', (string)$doc);
    }

    /**
     * @test
     */
    public function it_can_empty(): void
    {
        $doc = new DomElement();
        $doc->loadString('<div class="parent"><div class="firstchild"></div><div class="lastchild"></div></div>');

        $first = $doc->find('.parent')->first();

        $first->empty();

        $this->assertStringContainsString('<div class="parent"></div>', (string)$doc);
    }

    /**
     * @test
     */
    public function it_can_replace(): void
    {
        $doc = new DomElement();
        $doc->loadString('<div class="parent"><div class="firstchild"></div><div class="lastchild"></div></div>');

        $first = $doc->find('.firstchild')->first();
        $last = $doc->find('.lastchild')->first();

        $first->replace($last);

        $this->assertStringContainsString('<div class="parent"><div class="lastchild"></div></div>', (string)$doc);
    }

    /**
     * @test
     */
    public function it_no_breaks(): void
    {
        $doc = new DomElement(null, new \DOMText());

        $this->assertEquals('', (string)$doc);
    }

    /**
     * @test
     */
    public function it_can_load_html_files(): void
    {
        $doc = new DomElement();
        $doc->loadHTML(__DIR__ . '/../assets/captions.html');

        $this->assertEquals(static::$html, (string)$doc);
    }

    /**
     * @test
     */
    public function it_can_find_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString('<div class="parent"><div class="firstchild"></div><div class="lastchild"></div></div>');

        $parent = $doc->find('.parent');
        $last = $parent->find('.lastchild');

        $this->assertStringContainsString('<div class="lastchild"></div>', (string)$last);
    }

    /**
     * @test
     * @group xpath
     */
    public function it_can_find_xpath_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString('<div class="parent"><div class="firstchild"></div><div class="lastchild"></div></div>');

        $parent = $doc->find('.parent');
        $last = $parent->findxPath("descendant-or-self::*[@class and contains(concat(' ', normalize-space(@class), ' '), ' lastchild ')]");

        $this->assertStringContainsString('<div class="lastchild"></div>', (string)$last);
    }

    /**
     * @test
     * @group xpath
     */
    public function it_can_find_next_siblings_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString('<div class="parent"><div class="firstchild"></div><div class="lastchild"></div></div>');

        $first = $doc->find('.firstchild');
        $last = $first->nextSiblings();

        $this->assertStringContainsString('<div class="lastchild"></div>', (string)$last);
    }

    /**
     * @test
     * @group xpath
     */
    public function it_can_wrap_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString('<div class="parent"><div class="firstchild"></div><div class="lastchild"></div></div>');

        $first = $doc->find('.firstchild');
        $last = $doc->find('.lastchild')->first();

        $first->wrap($last);

        $this->assertStringContainsString('<div class="parent"><div class="lastchild"><div class="firstchild"></div></div></div>', (string)$doc);
    }

    /**
     * @test
     * @group xpath
     */
    public function it_can_before_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString('<div class="parent"><div class="firstchild"></div><div class="lastchild"></div></div>');

        $first = $doc->find('.firstchild');
        $last = $doc->find('.lastchild')->first();

        $first->before($last);

        $this->assertStringContainsString('<div class="parent"><div class="lastchild"></div><div class="firstchild"></div></div>', (string)$doc);
    }

    /**
     * @test
     * @group xpath
     */
    public function it_can_after_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString('<div class="parent"><div class="firstchild"></div><div class="lastchild"></div></div>');

        $first = $doc->find('.firstchild')->first();
        $last = $doc->find('.lastchild');

        $last->after($first);

        $this->assertStringContainsString('<div class="parent"><div class="lastchild"></div><div class="firstchild"></div></div>', (string)$doc);
    }

    /**
     * @test
     * @group xpath
     */
    public function it_can_append_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString('<div class="parent"><div class="firstchild"></div><div class="lastchild"></div></div>');

        $first = $doc->find('.firstchild');
        $last = $doc->find('.lastchild')->first();

        $first->append($last);

        $this->assertStringContainsString('<div class="parent"><div class="firstchild"><div class="lastchild"></div></div></div>', (string)$doc);
    }

    /**
     * @test
     * @group xpath
     */
    public function it_can_prepend_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString('<div class="parent"><div class="firstchild"></div><div class="lastchild"></div></div>');

        $first = $doc->find('.firstchild');
        $last = $doc->find('.lastchild')->first();

        $first->prepend($last);

        $this->assertStringContainsString('<div class="parent"><div class="firstchild"><div class="lastchild"></div></div></div>', (string)$doc);
    }

    /**
     * @test
     * @group xpath
     */
    public function it_can_empty_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString('<div class="parent"><div class="firstchild"></div><div class="lastchild"></div></div>');

        $parent = $doc->find('.parent');

        $parent->empty();

        $this->assertStringContainsString('<div class="parent"></div>', (string)$doc);
    }

    /**
     * @test
     * @group xpath
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

    /**
     * @test
     * @group xpath
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

        $tmp  = $first[0];
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
     * @group xpath
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
     * @param $html
     *
     * @return string
     */
    public function wrapCaptionableImages($html): string
    {
        $doc = new DomElement;
        $doc->loadString($html);

        $captions = $doc->find('.caption');

        $captions->map(static function (DomElement $caption) use ($doc) {
            $caption->find(':not(figure) > img')
                    ->map(static function (DomElement $image) use ($doc) {
                        $figure = $doc->create('figure');
                        $figcaption = $doc->create('figcaption');

                        $next_siblings = $image->nextSiblings();
                        $text = trim($next_siblings->text());

                        $next_siblings->remove();

                        $h1 = $doc->create('h1', $text);

                        $image->wrap($figure);
                        $figure->append($figcaption);
                        $figcaption->append($h1);
                    });
        });

        return (string)$doc;
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
        $doc = new DomElement;
        $doc->loadString($html);

        $doc->find('em > figure')
            ->map(static function (DomElement $figure) use ($doc) {
                $em = $figure->getParent();

                $em->after($figure);
            });

        return (string)$doc;
    }
}
