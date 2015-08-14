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

use BackBee\Bundle\LayoutBuilderBundle\Exception\LayoutCommandException;

/**
 * Remove Layout.
 *
 * @author      Nicolas Dufreche <nicolas.dufreche@lp-digital.fr>
 */
class RemoveLayoutCommand extends AbstractCommandLayout
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('layout:remove')
            ->addOption(
                'layout',
                null,
                InputOption::VALUE_REQUIRED,
                'layout name'
            )
            ->addOption(
                'site',
                null,
                InputOption::VALUE_OPTIONAL,
                'site label or URI'
            )
            ->setDescription('Removes existing layout.')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command remove named layout for a given site <error>only</error> if this layout is unused.

<info>php %command.full_name% --layout=layout-name --site=label|uri</info>
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

            $layout = $this->getLayout($layoutName, $site);

            if ($layout === null) {
                throw new LayoutCommandException($layoutName . ' layout not found.');
            }

            $page = $this->app
                         ->getEntityManager()
                         ->getRepository('BackBee\NestedNode\Page')
                         ->findOneBy(['_layout' => $layout]);

            if ($page !== null) {
                throw new LayoutCommandException($layoutName . ' layout can not be removed, because is already used');
            }
            $this->app->getEntityManager()->remove($layout);
            $this->app->getEntityManager()->flush();


            $output->writeln(sprintf('<fg=green>Layout %s deleted</fg=green>', $layoutName));
        } catch (\Exception $exception) {
            $this->exceptionHandler($output, $exception);
        }
    }
}
