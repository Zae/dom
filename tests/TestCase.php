<?php
declare(strict_types=1);

namespace Zae\DOM\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Class TestCase
 *
 * @package Zae\DOM\Tests
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * @param string $name
     * @param string $dataName
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct('DOM Testing Suite', $data, $dataName);
    }
}
