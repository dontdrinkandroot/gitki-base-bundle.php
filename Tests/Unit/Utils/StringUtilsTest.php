<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Unit\Utils;

use Dontdrinkandroot\GitkiBundle\Utils\StringUtils;
use PHPUnit\Framework\TestCase;

class StringUtilsTest extends TestCase
{
    public function testStartsWith(): void
    {
        $this->assertTrue(StringUtils::startsWith('bla', ''));
        $this->assertTrue(StringUtils::startsWith('bla', 'bl'));
        $this->assertfalse(StringUtils::startsWith('bla', 'la'));
    }

    public function testEndsWith(): void
    {
        $this->assertTrue(StringUtils::endsWith('bla', ''));
        $this->assertTrue(StringUtils::endsWith('bla', 'la'));
        $this->assertfalse(StringUtils::endsWith('bla', 'bl'));
    }

    public function testGetFirstChar(): void
    {
        $this->assertFalse(StringUtils::getFirstChar(''));
        $this->assertEquals('b', StringUtils::getFirstChar('bla'));
    }

    public function testGetLastChar(): void
    {
        $this->assertFalse(StringUtils::getLastChar(''));
        $this->assertEquals('a', StringUtils::getLastChar('bla'));
    }
}
