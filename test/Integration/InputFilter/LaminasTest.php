<?php

declare(strict_types=1);

namespace LaminasIntegrationTest\ApiTools\Admin\InputFilter;

use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterPluginManager;
use LaminasTest\ApiTools\Admin\Bootstrap;
use PHPUnit\Framework\TestCase;

use function array_keys;

class LaminasTest extends TestCase
{
    /**
     * @test
     */
    public function inputFilterServiceKeyWillReturnInputFilter()
    {
        $inputFilterManager = Bootstrap::getService(InputFilterPluginManager::class);
        $this->assertInstanceOf(InputFilterPluginManager::class, $inputFilterManager);

        $config                   = Bootstrap::getService('config');
        $configInputFilterAliases = $config['input_filters']['aliases'];
        $configInputFilterKeys    = array_keys($configInputFilterAliases);

        foreach ($configInputFilterKeys as $inputFilterKey) {
            $inputFilter = $inputFilterManager->get($inputFilterKey);
            $this->assertInstanceOf(InputFilter::class, $inputFilter);
        }
    }
}
