<?php

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
 *
 * @author Charles Rouillon <charles.rouillon@lp-digital.fr>
 */

namespace BackBee\Bundle\LayoutBuilderBundle\Entity;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

use BackBee\Bundle\LayoutBuilderBundle\Entity\UidGenerator\DefaultGenerator;
use BackBee\Bundle\LayoutBuilderBundle\Entity\UidGenerator\GeneratorInterface;
use BackBee\Bundle\LayoutBuilderBundle\Exception\LayoutYamlException;
use BackBee\Site\Layout;
use BackBee\Site\Site;

/**
 * @author Nicolas Dufreche <nicolas.dufreche@lp-digital.fr>
 */
class Builder
{
    /**
     * System extention config file.
     *
     * @var string
     */
    const EXTENSION = 'yml';

    /**
     * The uid generator to use
     * @var GeneratorInterface 
     */
    private $uidGenerator;
    
    /**
     * Sets the uid generator
     * 
     * @param  GeneratorInterface  $uidGenerator    The uid generator to use
     * 
     * @return Builder                              Returns the builder
     */
    public function setUidGenerator(GeneratorInterface $uidGenerator)
    {
        $this->uidGenerator = $uidGenerator;
        return $this;
    }

    /**
     * Gets the uid generator, sets it to default one if not set
     * 
     * @return GeneratorInterface
     */
    public function getUidGenerator()
    {
        if (null === $this->uidGenerator) {
            $this->setUidGenerator(new DefaultGenerator());
        }

        return $this->uidGenerator;
    }

    public function generateLayout($filename, Site $site = null, $extention = self::EXTENSION)
    {
        try {
            $data = Yaml::parse($filename);
            $uid = $this->getUidGenerator()->generateUid($filename, $data, $site);

            $layout = new Layout($uid);
            $layout->setPicPath($layout->getUid().'.png');

            if ($site !== null) {
                $layout->setSite($site);
            }

            if (array_key_exists('label', $data) && $data['label'] !== null) {
                $layout->setLabel($data['label']);
            } else {
                $layout->setLabel(basename($filename, '.'.self::EXTENSION));
            }

            if (array_key_exists('template', $data)) {
                $this->computeTemplate($layout, $data['template']);
            }

            if (array_key_exists('columns', $data)) {
                $layout->setData($this->computeColumns($data['columns']));
            } else {
                throw new LayoutYamlException(
                    'Layout '.$layout->getLabel().' definition need columns',
                    LayoutYamlException::NO_COLUMN_ERROR
                );
            }
         } catch (ParseException $e) {
            throw new LayoutYamlException(
                $e->getMessage(),
                LayoutYamlException::LAYOUT_BUILD_ERROR,
                $e,
                $e->getParsedFile(),
                $e->getParsedLine()
            );
        }

        return $layout;
    }

    public function computeTemplate($layout, $value)
    {
        if ($value !== null) {
            if (strlen(pathinfo($value, PATHINFO_EXTENSION)) !== 0) {
                $layout->setPath($value);
            } else {
                throw new LayoutYamlException(
                    'Invalid template name for '.$layout->getLabel().' layout.',
                    LayoutYamlException::FILE_EXTENSION_NOT_FOUND
                );
            }

        }
    }

    public function computeColumns($columns)
    {
        $data = [];
        $columnParser = new ColumnParser();

        foreach ($columns as $key => $column) {
            $column['title'] = $key;
            $data[] = $columnParser->parse($column);
        }

        return json_encode(['templateLayouts' => $data]);
    }
}
