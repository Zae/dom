<?php

declare(strict_types=1);

namespace Zae\DOM\Tests\Traits;

use Mockery as m;

trait useMocks
{
    public function tearDown(): void
    {
        parent::tearDown();

        if ($container = m::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }

        m::close();
    }
}
