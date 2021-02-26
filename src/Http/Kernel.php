<?php

namespace Tipoff\TestSupport\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * @var array
     */
    protected $middlewareGroups = [
        'api' => [
        ],
    ];

    protected $routeMiddleware = [
        'auth' => \Tipoff\TestSupport\Http\Middleware\Authenticate::class,
    ];
}
