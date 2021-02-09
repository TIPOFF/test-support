<?php

declare(strict_types=1);

namespace Tipoff\TestSupport;

use Orchestra\Testbench\TestCase as Orchestra;

abstract class BaseTestCase extends Orchestra
{
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

        $this->createStubTables();
    }

    public function getEnvironmentSetUp($app)
    {
        // Use a custom stub for the User model so it satisfies authentication
        $app['config']->set('tipoff.model_class.user', \Tipoff\TestSupport\Models\User::class);

        // Stub all models and nova resources not declared in the package or its dependencies
        $this->createStubModels()
            ->createStubNovaResources();
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

    protected function createStubTables(): self
    {
        // Create stub tables for stub models to satisfy possible FK dependencies
        foreach (config('tipoff.model_class') as $class) {
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
        foreach (config('tipoff.model_class') as $modelClass) {
            createModelStub($modelClass);
        }

        return $this;
    }

    protected function createStubNovaResources(): self
    {
        // Create nova resource stubs for anything not already defined
        foreach (config('tipoff.nova_class') as $alias => $novaClass) {
            if ($modelClass = config('tipoff.model_class.'.$alias)) {
                createNovaResourceStub($novaClass, $modelClass);
            }
        }

        return $this;
    }
}
