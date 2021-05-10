<?php

namespace LaminasTest\ApiTools\Admin\Controller\TestAsset;

use Laminas\Config\Writer\PhpArray as BaseWriter;

class ConfigWriter extends BaseWriter
{
    public $writtenFilename;
    public $writtenConfig;

    public function toFile($filename, $config, $exclusiveLock = true)
    {
        $this->writtenFilename = $filename;
        $this->writtenConfig = $config;
    }
}
