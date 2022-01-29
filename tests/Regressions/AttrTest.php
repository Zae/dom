<?php
declare(strict_types=1);

namespace Zae\DOM\Tests\Regressions;

use Zae\DOM\DomElement;
use Zae\DOM\Tests\TestCase;

/**
 * Class AttrTest
 * @package Zae\DOM\Tests\Regressions
 */
class AttrTest extends TestCase
{
    const html1 = '<div class="parent"></div>';

    /**
     * @test
     * @group collection
     */
    public function it_can_handle_attr_on_empty_collection(): void
    {
        $doc = new DomElement();
        $doc->loadString(static::html1);

        $emptycollection = $doc->find('.notexists');

        $emptycollection->attr('foo');

        static::assertTrue(true);
    }
}
