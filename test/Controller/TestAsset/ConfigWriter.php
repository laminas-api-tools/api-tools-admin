<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Controller\TestAsset;

use Laminas\Config\Writer\PhpArray as BaseWriter;

class ConfigWriter extends BaseWriter
{
    /** @var string */
    public $writtenFilename;

    /** @var mixed */
    public $writtenConfig;

    /**
     * @param string $filename
     * @param mixed $config
     * @param bool $exclusiveLock
     */
    public function toFile($filename, $config, $exclusiveLock = true): void
    {
        $this->writtenFilename = $filename;
        $this->writtenConfig   = $config;
    }
}
