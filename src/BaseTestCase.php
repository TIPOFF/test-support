<?php

declare(strict_types=1);

namespace Tipoff\TestSupport;

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\Str;
use Illuminate\Testing\TestView;
use Illuminate\View\DynamicComponent;
use Orchestra\Testbench\TestCase as Orchestra;
use Tipoff\Support\Contracts\Models\UserInterface;
use Tipoff\Support\TipoffPackage;
use Tipoff\Support\TipoffServiceProvider;
use Tipoff\TestSupport\Models\User;

abstract class BaseTestCase extends Orchestra
{
    use InteractsWithViews;

    protected bool $stubModels = true;

    protected bool $stubTables = true;

    protected bool $stubNovaResources = false;

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

        $this->artisan('view:clear')->run();
        $this->artisan('migrate', ['--database' => 'testing'])->run();

        if ($this->stubTables) {
            $this->createStubTables();
        }
    }

    public function getEnvironmentSetUp($app)
    {
        // Stub all models and nova resources not declared in the package or its dependencies
        if ($this->stubModels) {
            if (!class_exists($app['config']->get('tipoff.model_class.user'))) {
                // Use a custom stub for the User model so it satisfies authentication
                $app['config']->set('tipoff.model_class.user', \Tipoff\TestSupport\Models\User::class);
            }
            $this->createStubModels();
        }

        if ($this->stubNovaResources) {
            $this->createStubNovaResources();
        }
    }

    public function withViews(string $path, $app = null): self
    {
        if (!is_dir($path)) {
            throw new \Exception('Invalid view path: '.$path);
        }

        $app = $app ?: $this->app;
        $paths = array_merge($app['config']->get('view.paths'), [
            $path,
        ]);
        $app['config']->set('view.paths', $paths);

        return $this;
    }

    public function createApplication()
    {
        $app = parent::createApplication();

        $this->loadViewsFromTipoffPackages($app);

        return $app;
    }

    protected function loadViewsFromTipoffPackages($app): self
    {
        // Examine only the Tipoff Service providers
        foreach ($app->getProviders(TipoffServiceProvider::class) as $provider) {
            // Get the configured package from the provider
            $property = (new \ReflectionClass($provider))->getProperty('package');
            $property->setAccessible(true);

            /** @var TipoffPackage $package */
            $package = $property->getValue($provider);
            if ($package->hasViews()) {
                $views = $package->basePath('../resources/views');
                if (is_dir($views)) {
                    $this->withViews($views, $app);
                }
            }
        }

        return $this;
    }

    protected function blade(string $template, array $data = [])
    {
        $tempDirectory = sys_get_temp_dir();

        if (! in_array($tempDirectory, ViewFacade::getFinder()->getPaths())) {
            ViewFacade::addLocation(sys_get_temp_dir());
        }

        $tempFile = tempnam($tempDirectory, 'laravel-blade').'.blade.php';

        // Fix for Github Actions ci/cd on windows - this strips any .tmp from the tempfile name generated
        $tempFile = preg_replace('/\.tmp\.blade/', '.blade', $tempFile);

        file_put_contents($tempFile, $template);

        return new TestView(view(Str::before(basename($tempFile), '.blade.php'), $data));
    }


    /**
     * Useful to temporarily making logging output very visible during test execution for test
     * debugging purposes.
     */
    protected function logToStderr($app = null): self
    {
        $app = $app ?: $this->app;
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

    /**
     * Laravel's DynamicComponent uses statics to maintain state for quicker response. But,
     * statics create issues for tests - they can cause the test to behave differently based
     * on what took place in some prior test.  This clears the static's in the DynamicComponent
     * to make testing with it predictable.
     */
    protected function resetDynamicComponent(): self
    {
        $reflectionClass = new \ReflectionClass(DynamicComponent::class);

        $prop = $reflectionClass->getProperty('componentClasses');
        $prop->setAccessible(true);
        $prop->setValue([]);
        $prop = $reflectionClass->getProperty('compiler');
        $prop->setAccessible(true);
        $prop->setValue(null);

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
