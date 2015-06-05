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

use BackBee\Bundle\LayoutBuilderBundle\Entity\Builder;

use BackBee\Site\Layout;
use BackBee\Site\Site;

/**
 * @author Nicolas Dufreche <nicolas.dufreche@lp-digital.fr>
 */
class BuilderTest extends \PHPUnit_Framework_TestCase
{
    private $builder;

    private $baseDir;

    private $site;

    public function __construct()
    {
        $this->site = new Site();
        $this->baseDir = dirname(__DIR__).DIRECTORY_SEPARATOR.'Resources';
        $this->builder = new Builder($this->baseDir);
    }

    public function testGenerateLayout()
    {
        $layout = $this->builder->generateLayout($this->site, $this->baseDir.DIRECTORY_SEPARATOR.'Test.yml');

        $this->assertEquals(md5('Test.yml'), $layout->getUid());

        $this->assertEquals('Test.twig', $layout->getPath());
        $this->assertTrue(is_string($layout->getData()));
        $this->assertTrue(strlen($layout->getData()) !== 0);
        $this->assertEquals('Test Layout', $layout->getLabel());

        $decoded = json_decode($layout->getData());

        $this->assertObjectHasAttribute('templateLayouts', $decoded);
        $this->assertEquals(2, count($decoded->templateLayouts));
        $this->assertObjectHasAttribute('title', $decoded->templateLayouts[0]);

    }

    public function testGenerateLayoutAlt()
    {
        $layout = $this->builder->generateLayout($this->site, $this->baseDir.DIRECTORY_SEPARATOR.'TestAlt.yml');

        $this->assertEquals(md5('TestAlt.yml'), $layout->getUid());

        $this->assertNull($layout->getPath());
        $this->assertEquals('TestAlt', $layout->getLabel());
    }

    /**
     * @expectedException BackBee\Bundle\LayoutBuilderBundle\Entity\Exception\LayoutYamlException
     */
    public function  testGenerateLayoutExceptionInvalidTemplate()
    {
        $erroneousFolder = $this->baseDir.DIRECTORY_SEPARATOR.'Erroneous'.DIRECTORY_SEPARATOR;
        $this->builder->generateLayout($this->site, $erroneousFolder.'TestException1.yml');
    }

    /**
     * @expectedException BackBee\Bundle\LayoutBuilderBundle\Entity\Exception\LayoutYamlException
     */
    public function  testGenerateLayoutExceptionNoColumnDefinition()
    {
        $erroneousFolder = $this->baseDir.DIRECTORY_SEPARATOR.'Erroneous'.DIRECTORY_SEPARATOR;
        $this->builder->generateLayout($this->site, $erroneousFolder.'TestException2.yml');
    }
}
