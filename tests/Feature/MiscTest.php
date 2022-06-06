<?php

declare(strict_types=1);

namespace Zae\DOM\Tests\Feature;

use Symfony\Component\CssSelector\CssSelectorConverter;
use Zae\DOM\DomCollection;
use Zae\DOM\DomElement;
use Zae\DOM\Tests\TestCase;
use Zae\DOM\Tests\Traits\useMocks;

/**
 * Class DomModificationTest
 *
 * @package Zae\DOM\Tests\Feature
 */
class MiscTest extends TestCase
{
    use useMocks;

    private const html3 = '<div class="parent"><div class="firstchild"></div><div class="lastchild"></div></div>';

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
        $doc->loadString(self::html3);

        if ($selector !== null) {
            $selected = $doc->find($selector)->first();
            $found = $doc->find('.parent')->{$func}($selected);
        } elseif ($val1 !== null && $val2 !== null) {
            $found = $doc->find('.parent')->{$func}($val1, $val2);
        } elseif ($val1 !== null) {
            $found = $doc->find('.parent')->{$func}($val1);
        } else {
            $found = $doc->find('.parent')->{$func}();
        }

        static::assertEquals($type, gettype($found));
        if ($type === 'object') {
            static::assertEquals($class, get_class($found));
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
        $doc->loadString(self::html3);

        if ($selector !== null) {
            $selected = $doc->find($selector)->first();
            $found = $doc->find('.parent')->first()->{$func}($selected);
        } elseif ($val1 !== null && $val2 !== null) {
            $found = $doc->find('.parent')->first()->{$func}($val1, $val2);
        } elseif ($val1 !== null) {
            $found = $doc->find('.parent')->first()->{$func}($val1);
        } else {
            $found = $doc->find('.parent')->first()->{$func}();
        }

        static::assertEquals($type, gettype($found));
        if ($type === 'object') {
            static::assertEquals($class, get_class($found));
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
    public function it_accepts_a_css_selector_passed(): void
    {
        $mocked = \Mockery::mock(CssSelectorConverter::class)
            ->shouldReceive('toXpath')
            ->andReturn('descendant-or-self::*[@class and contains(concat(\' \', normalize-space(@class), \' \'), \' parent \')]')
            ->times(3)
            ->getMock();

        $doc = new DomElement($mocked);
        $doc->loadString(self::html3);

        $collection = $doc->find('.parent');
        $collection2 = $collection->find('.parent')[0];
        $html = $collection2->find('.parent')->first();

        static::assertEquals(self::html3 . "\n", $html->html());
    }

    /**
     * @test
     */
    public function it_resets_libxml_error_handling_after_string_loading(): void
    {
        $this->expectError();
        $this->expectErrorMessage('DOMDocument::loadHTML(): htmlParseStartTag: invalid element name in Entity, line: 1');
        libxml_use_internal_errors(false);

        $doc = new DomElement();
        $doc->loadString(self::html3);

        $dom = new \DOMDocument();
        $dom->loadHTML('<>aa<>aa<a>');
    }

    /**
     * @test
     */
    public function it_resets_libxml_error_handling_after_file_loading(): void
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
    public function it_resets_libxml_error_handling_after_xml_loading(): void
    {
        $this->expectError();
        $this->expectErrorMessage('DOMDocument::loadHTML(): htmlParseStartTag: invalid element name in Entity, line: 1');
        libxml_use_internal_errors(false);

        $doc = new DomElement();
        $doc->loadString(self::html3);

        $doc->find('.parent');

        $dom = new \DOMDocument();
        $dom->loadHTML('<>aa<>aa<a>');
    }

    /**
     * @test
     */
    public function it_sets_attributes(): void
    {
        $doc = new DomElement();
        $doc->loadString(self::html3);

        $parent = $doc->find('.parent')->first();
        $parent->attr('foo', 'bar');

        static::assertEquals("<div class=\"parent\" foo=\"bar\"><div class=\"firstchild\"></div><div class=\"lastchild\"></div></div>\n", (string)$doc);
    }

    /**
     * @test
     */
    public function it_gets_attributes(): void
    {
        $doc = new DomElement();
        $doc->loadString(self::html3);

        $parent = $doc->find('.parent')->first();
        $class = $parent->attr('class');

        static::assertEquals('parent', $class);
    }

    /**
     * @test
     */
    public function it_sets_attributes_on_collections(): void
    {
        $doc = new DomElement();
        $doc->loadString(self::html3);

        $collection = $doc->find('div');
        $collection->attr('foo', 'bar');

        static::assertEquals("<div class=\"parent\" foo=\"bar\"><div class=\"firstchild\" foo=\"bar\"></div><div class=\"lastchild\" foo=\"bar\"></div></div>\n", (string)$doc);
    }

    /**
     * @test
     */
    public function it_get_attributes_from_collections(): void
    {
        $doc = new DomElement();
        $doc->loadString(self::html3);

        $collection = $doc->find('div');
        $class = $collection->attr('class');

        static::assertEquals('parent', $class);
    }

    /**
     * @test
     * @group error
     *
     * @return void
     */
    public function it_throws_error_loadHTML_if_domdocument_is_node(): void
    {
        $this->expectExceptionMessage('You can only loadHTML on a root instance.');

        $doc = new DomElement(
            null,
            new \DOMNode()
        );

        $doc->loadHTML(__DIR__ . '/../assets/captions.html');
    }

    /**
     * @test
     * @group error
     *
     * @return void
     */
    public function it_throws_error_create_if_domdocument_is_node(): void
    {
        $this->expectExceptionMessage('You can only call create on a root instance');

        $doc = new DomElement(
            null,
            new \DOMNode()
        );

        $doc->create('a');
    }

    /**
     * @test
     * @group error
     *
     * @return void
     */
    public function it_throws_error_attr_if_domdocument_is_node(): void
    {
        $this->expectExceptionMessage('This element does not support attributes');

        $doc = new DomElement(
            null,
            new \DOMNode()
        );

        $doc->attr('a');
    }

    /**
     * @test
     * @group error
     *
     * @return void
     */
    public function it_reloads_xml_struct(): void
    {
        global $simplexml_called;
        $simplexml_called = false;

        $doc = new DomElement();
        $doc->loadString(self::html3);

        $doc->findxPath('*');

        static::assertTrue($simplexml_called);
    }

    /**
     * @test
     * @group error
     * @group bla
     *
     * @return void
     */
    public function it_does_not_reload_xml_struct_when_wrong_domelement(): void
    {
        global $simplexml_called;
        $simplexml_called = false;

        try {
            $doc = new DomElement(
                null,
                new \DOMNode()
            );

            $doc->findxPath('*');
        } catch (\Exception $e) {
            static::assertEquals('Document does not support xpath', $e->getMessage());
        }

        static::assertFalse($simplexml_called);
    }
}

namespace Zae\Dom;

/**
 * Sneaky namespaced version of simplexml_import_dom which our
 * code will call because we did not give PHP a namespace to call
 * so it will load this namespaced version before the global version.
 *
 * @param $dom
 * @return \$1|\SimpleXMLElement|null
 */
function simplexml_import_dom($dom)
{
    global $simplexml_called;
    $simplexml_called = true;
    return \simplexml_import_dom($dom);
}
