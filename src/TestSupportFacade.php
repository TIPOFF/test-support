<?php

namespace Tipoff\TestSupport;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Tipoff\TestSupport\TestSupport
 */
class TestSupportFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'test-support';
    }
}
