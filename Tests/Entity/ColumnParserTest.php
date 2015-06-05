<?php
namespace BackBee\Bundle\LayoutBuilderBundle\Tests\Entity;

/*
 * Copyright (c) 2011-2015 Lp digital system
 *
 * This file is part of BackBee.
 *
 * BackBee is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * BackBee is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with BackBee. If not, see <http://www.gnu.org/licenses/>.
 */

use BackBee\Bundle\LayoutBuilderBundle\Entity\ColumnParser;

/**
 * @author Nicolas Dufreche <nicolas.dufreche@lp-digital.fr>
 */
class ColumnParserTest extends \PHPUnit_Framework_TestCase
{
    private $parser;

    public function setUp()
    {
        $this->parser = new ColumnParser();
    }

    public function testParsingDefaultData()
    {
        $result = $this->parser->parse([]);

        $this->assertFalse($result['mainZone']);
        $this->assertFalse($result['inherited']);
        $this->assertEquals([''], $result['accept']);
        $this->assertSame(0, $result['maxentry']);
        $this->assertNull($result['defaultClassContent']);
    }

    public function testGetBooleanValue()
    {
        $result = $this->parser->parse(['mainZone' => false, 'inherited' => 1]);

        $this->assertFalse($result['mainZone']);
        $this->assertTrue($result['inherited']);
    }


    public function testGetAccept()
    {
        $result = $this->parser->parse(['accept' => null]);
        $this->assertEquals([''], $result['accept']);

        $result = $this->parser->parse(['accept' => 'TestContent']);
        $this->assertEquals(['TestContent'], $result['accept']);

        $result = $this->parser->parse(['accept' => ['TestContent1', 'TestContent2']]);
        $this->assertEquals(['TestContent1', 'TestContent2'], $result['accept']);
    }

    public function testGetMaxEntry()
    {
        $result = $this->parser->parse(['maxentry' => null]);
        $this->assertSame(0, $result['maxentry']);

        $result = $this->parser->parse(['maxentry' => '2']);
        $this->assertSame(2, $result['maxentry']);
    }


    public function testGetDefault()
    {
        $result = $this->parser->parse(['defaultClassContent' => null]);
        $this->assertNull($result['defaultClassContent']);

        $result = $this->parser->parse(['defaultClassContent' => 'ContentSet']);
        $this->assertEquals('ContentSet', $result['defaultClassContent']);
    }

    public function testCompatibility()
    {
        $result = $this->parser->parse([]);
        $this->assertArrayHasKey('layoutSize', $result);
        $this->assertArrayHasKey('gridSizeInfos', $result);
        $this->assertArrayHasKey('layoutClass', $result);
        $this->assertArrayHasKey('animateResize', $result);
        $this->assertArrayHasKey('showTitle', $result);
        $this->assertArrayHasKey('target', $result);
        $this->assertArrayHasKey('id', $result);
    }
}
