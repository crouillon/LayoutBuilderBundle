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

use BackBee\Bundle\LayoutBuilderBundle\Entity\Builder;
use BackBee\Bundle\LayoutBuilderBundle\Exception\LayoutCommandException;
use BackBee\Bundle\LayoutBuilderBundle\Exception\LayoutYamlException;

use BackBee\Console\AbstractCommand;
use BackBee\Site\Layout;

use Symfony\Component\Console\Output\OutputInterface;
/**
 * Create Layout.
 *
 * @copyright   Lp digital system
 * @author      Nicolas Dufreche <nicolas.dufreche@lp-digital.fr>
 */
class AbstractCommandLayout extends AbstractCommand
{
    protected $app;
    protected $repo;
    protected $baseDir;
    protected $builder;

    protected function init()
    {
        $this->app = $this->getContainer()->get('bbapp');
        $this->repo = $this->app->getEntityManager()->getRepository('BackBee\Site\Layout');

        $config = $this->app->getContainer()->get('bundle.layoutbuilder.config')->getBundleConfig();
        if (!array_key_exists('definition_folder', $config)) {
            throw new LayoutCommandException('No definition folder define in LayoutBuilderConfig');
        }
        $this->baseDir = $this->app->getBaseRepository().DIRECTORY_SEPARATOR.$config['definition_folder'];
        $this->builder = new Builder();
    }

    public function getSite($identifier = null)
    {
        if ($identifier === null) {
            return null;
        }
        $repo = $this->app->getEntityManager()->getRepository('BackBee\Site\Site');

        $site = $repo->findOneBy(['_label' => $identifier]);
        if ($site !== null) {
            return $site;
        }

        $site = $repo->findOneBy(['_server_name' => $identifier]);
        if ($site !== null) {
            return $site;
        }

        throw new LayoutCommandException($identifier . ' site not found');
    }

    public function buildLayout($layout, $site)
    {
        $file = $this->getFileInDefinitionFolder($layout);

        return $this->builder->generateLayout($file, $site);
    }

    public function buildFromFolder($site, $overide = false)
    {
        $files = scandir($this->getFileInDefinitionFolder());

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === Builder::EXTENSION) {
                if (
                    $overide ||
                    (!$overide && !$this->isLayoutExist(basename($file), $site))
                ) {
                    $layout = $this->buildLayout($file, $site);
                    if ($overide) {
                         $layout = $this->update(basename($file), $layout, $site);
                    }
                    $this->persist($layout);
                }
            }
        }
    }

    public function getFileInDefinitionFolder($filename = '')
    {
        if (strlen($filename) > strlen($this->baseDir)) {
            return $filename;
        } else {
            if (
                $filename === '' ||
                pathinfo($filename, PATHINFO_EXTENSION) === Builder::EXTENSION
            ) {
                return $this->baseDir.DIRECTORY_SEPARATOR.$filename;
            } else {
                return $this->baseDir.DIRECTORY_SEPARATOR.$filename.'.'.Builder::EXTENSION;
            }
        }
    }

    public function getLayout($layout, $site) {
        $siteUid = ($site instanceof Site) ? $site->getUid() : '';
        $fileName = (pathinfo($layout, PATHINFO_EXTENSION) === Builder::EXTENSION) ? $layout : $layout.'.'.Builder::EXTENSION;
        return $this->repo->find(md5($siteUid.$fileName));
    }

    public function isLayoutExist($layout, $site)
    {
        return ($this->getSite() !== null);
    }

    public function update($layoutName, $layout, $site)
    {
        $old = $this->getLayout($layoutName, $site);
        if ($old === null) {
            return $layout;
        }

        $old->setLabel($layout->getLabel())
            ->setData($layout->getData())
            ->setPath($layout->getPath());

        return $old;
    }

    public function persist(Layout $layout)
    {
        $this->app->getEntityManager()->persist($layout);
        $this->app->getEntityManager()->flush($layout);
    }

    public function exceptionHandler(OutputInterface $output, \Exception $exception)
    {
        if (
            $exception instanceof LayoutCommandException ||
            $exception instanceof LayoutYamlException
        ) {
            $output->writeln("\n".$exception->getMessage()."\n");
        } else {
            $output->writeln("\n".'Internal error'."\n");
        }
    }
}