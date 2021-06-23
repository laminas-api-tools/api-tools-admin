<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\TestAsset;

/**
 * Class for spying on invokables.
 *
 * Mock this class when you need to mock a closure or invokable class, and do
 * assertions against the `call()` method. Have containers return
 * `[$instance->reveal(), 'call']`.
 */
class Closure
{
    /**
     * @return mixed
     */
    public function call()
    {
    }
}
