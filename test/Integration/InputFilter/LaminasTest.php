<?php

declare(strict_types=1);

namespace LaminasIntegrationTest\ApiTools\Admin\InputFilter;

use Exception;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\InputFilter\InputFilterPluginManager;
use LaminasTest\ApiTools\Admin\Bootstrap;
use PHPUnit\Framework\TestCase;

use function array_keys;

class LaminasTest extends TestCase
{
    /**
     * @test
     * @throws Exception
     */
    public function inputFilterServiceKeyWillReturnInputFilter(): void
    {
        $inputFilterConfig = $this->getInputFilterAliases();
        $inputFilterKeys = array_keys($inputFilterConfig);

        /** @var string $key */
        foreach ($inputFilterKeys as $key) {
            $inputFilter = $this->getInputFilter($key);
            $this->assertInstanceOf(InputFilter::class, $inputFilter);
        }
    }

    private function getInputFilterManager(): InputFilterPluginManager
    {
        $inputFilterManager = Bootstrap::getService(InputFilterPluginManager::class);

        if(!$inputFilterManager instanceof InputFilterPluginManager) {
            throw new Exception('Invalid class.');
        }

        return $inputFilterManager;
    }

    /**
     * @throws Exception
     */
    private function getInputFilter(string $name): InputFilterInterface
    {
        $inputFilterManager = $this->getInputFilterManager();
        $inputFilter = $inputFilterManager->get($name);

        if(! $inputFilter instanceof InputFilterInterface) {
            throw new Exception('Invalid class.');
        }

        return $inputFilter;
    }

    private function getInputFilterConfig(): array
    {
        $config = Bootstrap::getConfig();

        return is_array($config['input_filters'])? $config['input_filters']: [];
    }

    private function getInputFilterAliases(): array
    {
        $config = $this->getInputFilterConfig();

        return is_array($config['aliases'])? $config['aliases']: [];
    }
}
