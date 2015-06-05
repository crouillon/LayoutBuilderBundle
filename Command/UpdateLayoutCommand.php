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

namespace BackBee\Bundle\LayoutBuilderBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Update Layout.
 *
 * @copyright   Lp digital system
 * @author      Nicolas Dufreche <nicolas.dufreche@lp-digital.fr>
 */
class UpdateLayoutCommand extends AbstractCommandLayout
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('layout:update')
            ->addOption(
                'layout',
                null,
                InputOption::VALUE_REQUIRED,
                'layout file name'
            )
            ->addOption(
                'site',
                null,
                InputOption::VALUE_OPTIONAL,
                'site label or URI'
            )
            ->setDescription('Update existant backbee layout')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command update layout based on layout definition name and for a given site

<info>php %command.full_name% --layout=file-name-definition --site=label|uri</info>
EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init();

        $layoutName = $input->getOption('layout');
        $site = $input->getOption('site');

        try {
            $site = $this->getSite($site);

            if ($layoutName === null) {
                $this->buildFromFolder($site, true);
                $output->writeln("\nLayouts updated.\n");
            } else {
                $layout = $this->buildLayout($layoutName, $site);
                $layout = $this->update($layoutName, $layout, $site);
                $this->persist($layout);
                $output->writeln("\nLayout updated.\n");
            }

        } catch (\Exception $exception) {
            $this->exceptionHandler($output, $exception);
        }
    }
}