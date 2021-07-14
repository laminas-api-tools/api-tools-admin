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
    public function inputFilterServiceKeyWillReturnInputFilter(): void
    {
        /**
         * @psalm-suppress MixedAssignment
         */
        $inputFilterManager = Bootstrap::getService(InputFilterPluginManager::class);
        $inputFilterConfig = Bootstrap::getConfig('input_filters');
        /**
         * @psalm-suppress MixedArgument
         */
        $inputFilterKeys = array_keys($inputFilterConfig['aliases']);

        foreach ($inputFilterKeys as $key) {
            if(!is_string($key)) {
                throw new \UnexpectedValueException('$key must be a string.');
            }

            /**
             * @psalm-suppress MixedAssignment
             * @psalm-suppress MixedMethodCall
             */
            $inputFilter = $inputFilterManager->get($key);
            $this->assertInstanceOf(InputFilter::class, $inputFilter);
        }
    }
}
