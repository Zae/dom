<?php
declare(strict_types=1);

namespace Zae\DOM\Tests\Feature;

use Symfony\Component\CssSelector\CssSelectorConverter;
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

    /**
     * @test
     */
    public function it_accepts_a_css_selector_passed()
    {
        $mocked = \Mockery::mock(CssSelectorConverter::class)
            ->shouldReceive('toXpath')
            ->andReturn('descendant-or-self::*[@class and contains(concat(\' \', normalize-space(@class), \' \'), \' parent \')]')
            ->times(3)
            ->getMock();

        $doc = new DomElement($mocked);
        $doc->loadString(static::html3);

        $collection = $doc->find('.parent');
        $collection2 = $collection->find('.parent')[0];
        $html = $collection2->find('.parent')->first();

        $this->assertEquals(static::html3 . "\n", $html->html());

        \Mockery::close();
    }

    /**
     * @test
     */
    public function it_resets_libxml_error_handling_after_string_loading()
    {
        $this->expectError();
        $this->expectErrorMessage('DOMDocument::loadHTML(): htmlParseStartTag: invalid element name in Entity, line: 1');
        libxml_use_internal_errors(false);

        $doc = new DomElement();
        $doc->loadString(static::html3);

        $dom = new \DOMDocument();
        $dom->loadHTML('<>aa<>aa<a>');
    }

    /**
     * @test
     */
    public function it_resets_libxml_error_handling_after_file_loading()
    {
        $this->expectError();
        $this->expectErrorMessage('DOMDocument::loadHTML(): htmlParseStartTag: invalid element name in Entity, line: 1');
        libxml_use_internal_errors(false);

        $doc = new DomElement();
        $doc->loadHTML(__DIR__ . '/../assets/captions.html');

        $dom = new \DOMDocument();
        $dom->loadHTML('<>aa<>aa<a>');
    }

    /**
     * @test
     */
    public function it_resets_libxml_error_handling_after_xml_loading()
    {
        $this->expectError();
        $this->expectErrorMessage('DOMDocument::loadHTML(): htmlParseStartTag: invalid element name in Entity, line: 1');
        libxml_use_internal_errors(false);

        $doc = new DomElement();
        $doc->loadString(static::html3);

        $doc->find('.parent');

        $dom = new \DOMDocument();
        $dom->loadHTML('<>aa<>aa<a>');
    }
}
