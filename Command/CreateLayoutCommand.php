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

use BackBee\Bundle\LayoutBuilderBundle\Exception\LayoutCommandException;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Create Layout.
 *
 * @copyright   Lp digital system
 * @author      Nicolas Dufreche <nicolas.dufreche@lp-digital.fr>
 */
class CreateLayoutCommand extends AbstractCommandLayout
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('layout:create')
            ->addOption(
                'layout',
                null,
                InputOption::VALUE_OPTIONAL,
                'layout file name'
            )
            ->addOption(
                'site',
                null,
                InputOption::VALUE_OPTIONAL,
                'site label or URI'
            )
            ->setDescription('Create backbee layout')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command create layout based on layout definition and for a given site

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

        $layout = $input->getOption('layout');
        $site = $input->getOption('site');

        try {
            $site = $this->getSite($site);

            if ($layout === null) {
                $this->buildFromFolder($site);
                $output->writeln("\nLayouts created.\n");
            } else {
                if ($this->isLayoutExist($layout, $site)) {
                    throw new LayoutCommandException('layout '.$layout.' already exists');
                }

                $layout = $this->buildLayout($layout, $site);
                $this->persist($layout);
                $output->writeln("\nLayout created.\n");
            }

        } catch (\Exception $exception) {
            $this->exceptionHandler($output, $exception);
        }
    }
}