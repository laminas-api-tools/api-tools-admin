<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

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
    public function call()
    {
    }
}
