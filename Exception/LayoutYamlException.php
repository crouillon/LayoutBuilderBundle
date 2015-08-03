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

namespace BackBee\Bundle\LayoutBuilderBundle\Exception;

use BackBee\Exception\BBException;

/**
 * Yaml exceptions.
 *
 * @author     Nicolas Dufreche <nicolas.dufreche@lp-digital.fr>
 */
class LayoutYamlException extends BBException
{
    /**
     * The configuration file can not be parse.
     *
     * @var int
     */
    const LAYOUT_BUILD_ERROR = 4400;

    /**
     * The layout template haven't extension.
     *
     * @var int
     */
    const FILE_EXTENSION_NOT_FOUND = 4404;

    /**
     * No column found in layout defination.
     *
     * @var int
     */
    const NO_COLUMN_ERROR = 4500;
}
