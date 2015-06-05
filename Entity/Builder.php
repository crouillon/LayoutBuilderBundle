<?php
namespace BackBee\Bundle\LayoutBuilderBundle\Entity;

use BackBee\Bundle\LayoutBuilderBundle\Exception\LayoutYamlException;

use BackBee\Site\Layout;
use BackBee\Site\Site;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

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

    public function generateLayout($filename, Site $site = null, $extention = self::EXTENSION)
    {
        try {
            $data = Yaml::parse($filename);
            $baseUid = ($site !== null) ? $site->getUid() : '';

            $layout = new Layout(md5($baseUid . basename($filename)));
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
                    'Invalid template name for '.$layout->getLabel().' layout',
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
