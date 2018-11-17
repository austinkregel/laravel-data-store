<?php

namespace Kregel\Skel\Tests;

use Kregel\Skel\Skel;
use PHPUnit\Framework\TestCase;

/**
 * Class ExampleTest
 * @package Kregel\Skel\Tests
 */
class ExampleTest extends TestCase
{
    public function testTrueIsTrue()
    {
        $this->assertTrue((new Skel)->returnTrue());
    }
}
