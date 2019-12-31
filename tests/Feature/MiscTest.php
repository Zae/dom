<?php
declare(strict_types=1);

namespace Zae\DOM\Tests\Feature;

use Symfony\Component\CssSelector\CssSelectorConverter;
use Zae\DOM\DomCollection;
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
     * @dataProvider chainCollection
     */
    public function collections_can_chain($func, $type, $class, string $selector = null, $val1 = null, $val2 = null): void
    {
        $doc = new DomElement();
        $doc->loadString(static::html3);

        if ($selector !== null) {
            $selected = $doc->find($selector)->first();
            $found = $doc->find('.parent')->{$func}($selected);
        } elseif($val1 !== null && $val2 !== null) {
            $found = $doc->find('.parent')->{$func}($val1, $val2);
        } elseif($val1 !== null) {
            $found = $doc->find('.parent')->{$func}($val1);
        } else {
            $found = $doc->find('.parent')->{$func}();
        }

        $this->assertEquals($type, gettype($found));
        if ($type === 'object') {
            $this->assertEquals($class, get_class($found));
        }
    }

    /**
     * @test
     * @group modify
     * @dataProvider chainElement
     */
    public function elements_can_chain($func, $type, $class, string $selector = null, $val1 = null, $val2 = null): void
    {
        $doc = new DomElement();
        $doc->loadString(static::html3);

        if ($selector !== null) {
            $selected = $doc->find($selector)->first();
            $found = $doc->find('.parent')->first()->{$func}($selected);
        } elseif($val1 !== null && $val2 !== null) {
            $found = $doc->find('.parent')->first()->{$func}($val1, $val2);
        } elseif($val1 !== null) {
            $found = $doc->find('.parent')->first()->{$func}($val1);
        } else {
            $found = $doc->find('.parent')->first()->{$func}();
        }

        $this->assertEquals($type, gettype($found));
        if ($type === 'object') {
            $this->assertEquals($class, get_class($found));
        }
    }

    /**
     * @return array
     */
    public function chainCollection(): array
    {
        return [
            ['first', 'object', DomElement::class, null],
            ['wrap', 'object', DomCollection::class, '.firstchild'],
            ['attr', 'string', null, null, 'class'],
            ['attr', 'object', DomCollection::class, null, 'bar', 'baz'],
        ];
    }

    /**
     * @return array
     */
    public function chainElement(): array
    {
        return [
            ['wrap', 'object', DomElement::class, '.firstchild'],
            ['attr', 'string', null, null, 'class'],
            ['attr', 'object', DomElement::class, null, 'bar', 'baz'],
        ];
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

    /**
     * @test
     */
    public function it_sets_attributes()
    {
        $doc = new DomElement();
        $doc->loadString(static::html3);

        $parent = $doc->find('.parent')->first();
        $parent->attr('foo', 'bar');

        $this->assertEquals("<div class=\"parent\" foo=\"bar\"><div class=\"firstchild\"></div><div class=\"lastchild\"></div></div>\n", (string)$doc);
    }

    /**
     * @test
     */
    public function it_gets_attributes()
    {
        $doc = new DomElement();
        $doc->loadString(static::html3);

        $parent = $doc->find('.parent')->first();
        $class = $parent->attr('class');

        $this->assertEquals('parent', $class);
    }

    /**
     * @test
     */
    public function it_sets_attributes_on_collections()
    {
        $doc = new DomElement();
        $doc->loadString(static::html3);

        $collection = $doc->find('div');
        $collection->attr('foo', 'bar');

        $this->assertEquals("<div class=\"parent\" foo=\"bar\"><div class=\"firstchild\" foo=\"bar\"></div><div class=\"lastchild\" foo=\"bar\"></div></div>\n", (string)$doc);
    }

    /**
     * @test
     */
    public function it_get_attributes_from_collections()
    {
        $doc = new DomElement();
        $doc->loadString(static::html3);

        $collection = $doc->find('div');
        $class = $collection->attr('class');

        $this->assertEquals('parent', $class);
    }
}
