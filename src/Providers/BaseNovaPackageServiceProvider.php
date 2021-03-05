<?php

declare(strict_types=1);

namespace Tipoff\TestSupport\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;

abstract class BaseNovaPackageServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * To test Nova resources within a package, override this base Provider and update
     * the packageResources array with the Nova resource classes provided by the package.
     * NOTE: Resources registered via package provider definitions arent required here.
     *
     * Ensure both of
     *   NovaCoreServiceProvider::class,
     *   NovaTestbenchServiceProvider::class,
     */
    public static array $packageResources = [

    ];

    protected function resources()
    {
        Nova::resources(array_filter(
            array_merge(config('tipoff.nova_class'), self::$packageResources),
            function (string $class) {
                return class_exists($class);
            })
        );
    }

    protected function routes()
    {
        Nova::routes()
            ->register();
    }

    protected function gate()
    {
        Gate::define('viewNova', function () {
            return true;
        });
    }
}
