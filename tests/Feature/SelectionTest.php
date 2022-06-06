<?php

declare(strict_types=1);

namespace Zae\DOM\Tests\Feature;

use Symfony\Component\CssSelector\CssSelectorConverter;
use Symfony\Component\CssSelector\Exception\ExpressionErrorException;
use Zae\DOM\DomCollection;
use Zae\DOM\DomElement;
use Zae\DOM\Tests\TestCase;

/**
 * Class CssSelectionTest
 *
 * @package Zae\DOM\Tests\Feature
 */
class SelectionTest extends TestCase
{
    private static $html = <<<'HTML'
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

    private const html3 = '<div class="parent"><div class="firstchild"></div><div class="lastchild"></div></div>';

    /**
     * @test
     * @group select
     * @group css
     */
    public function it_can_use_basic_css_selector(): void
    {
        $doc = new DomElement();
        $doc->loadString(self::$html);

        $captions = $doc->find('.caption');

        static::assertCount(2, $captions);
        static::assertStringContainsString('<div class="caption">', (string)$captions);
    }

    /**
     * @test
     * @group select
     * @group css
     */
    public function it_can_use_nested_css_selector(): void
    {
        $string = $this->wrapCaptionableImages(self::$html);

        static::assertStringContainsString('<figcaption><h1>CAPTION 1</h1></figcaption>', $string);
        static::assertStringContainsString('<figcaption><h1>CAPTION 2</h1></figcaption>', $string);
    }

    /**
     * @test
     * @group select
     */
    public function it_can_find_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString(self::html3);

        $parent = $doc->find('.parent');
        $last = $parent->find('.lastchild');

        static::assertStringContainsString('<div class="lastchild"></div>', (string)$last);
    }

    /**
     * @test
     * @group select
     * @group xpath
     */
    public function it_can_find_xpath_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString(self::html3);

        $parent = $doc->find('.parent');
        $last = $parent->findxPath("descendant-or-self::*[@class and contains(concat(' ', normalize-space(@class), ' '), ' lastchild ')]");

        static::assertStringContainsString('<div class="lastchild"></div>', (string)$last);
    }

    /**
     * @test
     * @group load
     */
    public function it_can_load_html_files(): void
    {
        $doc = new DomElement();
        $doc->loadHTML(__DIR__ . '/../assets/captions.html');

        static::assertEquals(self::$html, (string)$doc);
    }

    /**
     * @test
     * @group pseudo
     */
    public function it_can_use_pseudo_selectors(): void
    {
        $doc = new DomElement();
        $doc->loadString('<div><a href="#">LINK</a></div>');

        $link = $doc->find('*:link');

        static::assertEquals("<a href=\"#\">LINK</a>\n", (string)$link);
    }

    /**
     * @test
     * @group pseudo
     */
    public function it_can_use_pseudo_selectors_in_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString('<div><a href="#">LINK</a></div>');
        $els = $doc->find('div')->elements()->toArray();

        $col = (new DomCollection($els))[0];

        $link = $col->find('*:link');

        static::assertEquals("<a href=\"#\">LINK</a>\n", (string)$link);
    }

    /**
     * @test
     * @group pseudo
     */
    public function it_can_use_pseudo_selectors_only_when_requested(): void
    {
        $this->expectException(ExpressionErrorException::class);
        $this->expectErrorMessage('Pseudo-class "link" not supported.');

        $doc = new DomElement(new CssSelectorConverter(false));
        $doc->loadString('<div><a href="#">LINK</a></div>');

        $link = $doc->find('*:link');

        static::assertEquals("<a href=\"#\">LINK</a>\n", (string)$link);
    }

    /**
     * @param $html
     *
     * @return string
     */
    private function wrapCaptionableImages($html): string
    {
        $doc = new DomElement();
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
}
