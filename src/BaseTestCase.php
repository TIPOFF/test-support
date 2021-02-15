<?php

declare(strict_types=1);

namespace Tipoff\TestSupport;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Orchestra\Testbench\TestCase as Orchestra;
use Tipoff\Support\Contracts\Models\UserInterface;
use Tipoff\TestSupport\Models\User;

abstract class BaseTestCase extends Orchestra
{
    protected bool $stubModels = true;

    protected bool $stubTables = true;

    protected bool $stubNovaResources = true;

    /**
     * When testing Nova, create a custom override of BaseNovaPackageServiceProvider
     * that defines the Nova resources in the package and then make that custom provider
     * and the core nova provider are included in your `getPackageProviders($app)` array:
     * eg.
     *   NovaCoreServiceProvider::class,
     *   NovaTestbenchServiceProvider::class,
     *   // Other package providers
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--database' => 'testing'])->run();

        if ($this->stubTables) {
            $this->createStubTables();
        }
    }

    public function getEnvironmentSetUp($app)
    {
        // Stub all models and nova resources not declared in the package or its dependencies
        if ($this->stubModels) {
            // Use a custom stub for the User model so it satisfies authentication
            $app['config']->set('tipoff.model_class.user', \Tipoff\TestSupport\Models\User::class);
            $this->createStubModels();
        }

        if ($this->stubNovaResources) {
            $app['config']->set('tipoff.nova_class.user', \Tipoff\TestSupport\Nova\User::class);
            $this->createStubNovaResources();
        }
    }

    /**
     * Useful to temporarily making logging output very visible during test execution for test
     * debugging purposes.
     */
    protected function logToStderr($app): self
    {
        $app['config']->set('logging.default', 'stderr');

        return $this;
    }

    protected function logSql(): self
    {
        DB::listen(function($query) {
            Log::info(
                $query->sql,
                $query->bindings
            );
        });

        return $this;
    }

    protected function createStubTables(): self
    {
        // Create stub tables for stub models to satisfy possible FK dependencies
        foreach (config('tipoff.model_class') ?? [] as $class) {
            if (method_exists($class, 'createTable')) {
                /** @psalm-suppress UndefinedClass */
                $class::createTable();
            }
        }

        return $this;
    }

    protected function createStubModels(): self
    {
        // Create stub models for anything not already defined
        foreach (config('tipoff.model_class') ?? [] as $modelClass) {
            createModelStub($modelClass);
        }

        return $this;
    }

    protected function createStubNovaResources(): self
    {
        // Create nova resource stubs for anything not already defined
        foreach (config('tipoff.nova_class') ?? [] as $alias => $novaClass) {
            if ($modelClass = config('tipoff.model_class.'.$alias)) {
                createNovaResourceStub($novaClass, $modelClass);
            }
        }

        return $this;
    }

    static protected function createPermissionedUser(string $permission, bool $hasPermission): UserInterface
    {
        /**
         * Normally, this would be done with a makePartial() mock, but the mock gets lost
         * and the real user class is used when the permission method is invoked.  So, we
         * establish expectations in directly in an authenticatable class instance.
         */
        $user = new class extends User {
            private string $permission;
            private bool $hasPermission;

            public function hasPermissionTo($permission, $guardName = null): bool
            {
                return $this->permission === $permission ? $this->hasPermission : false;
            }

            public function setHasPermission(string $permission, bool $hasPermission): self
            {
                $this->permission = $permission;
                $this->hasPermission = $hasPermission;

                return $this;
            }
        };

        return $user->setHasPermission($permission, $hasPermission);
    }
}
