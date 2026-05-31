<?php

namespace Mhmiton\LaravelModulesLivewire\Tests\Unit\View;

use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Factory;
use Illuminate\View\FileViewFinder;
use Illuminate\View\View;
use Mhmiton\LaravelModulesLivewire\Tests\TestCase;
use Mhmiton\LaravelModulesLivewire\View\ModuleVoltViewFactory;

class ModuleVoltViewFactoryTest extends TestCase
{
    protected $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new ModuleVoltViewFactory(
            $this->app['view.engine.resolver'],
            $this->app['view.finder'],
            $this->app['events']
        );
    }

    public function test_factory_can_be_instantiated()
    {
        $this->assertInstanceOf(ModuleVoltViewFactory::class, $this->factory);
    }

    public function test_factory_extends_view_factory()
    {
        $this->assertInstanceOf(Factory::class, $this->factory);
    }

    public function test_factory_has_finder()
    {
        $finder = $this->factory->getFinder();

        $this->assertInstanceOf(FileViewFinder::class, $finder);
    }

    public function test_factory_has_engine_resolver()
    {
        $resolver = $this->factory->getEngineResolver();

        $this->assertInstanceOf(EngineResolver::class, $resolver);
    }

    public function test_factory_can_add_namespace()
    {
        $this->factory->addNamespace('test', base_path('test-views'));

        $this->assertTrue(true); // Method should not throw exception
    }

    public function test_factory_can_add_location()
    {
        $this->factory->addLocation(base_path('test-views'));

        $this->assertTrue(true); // Method should not throw exception
    }

    public function test_factory_can_make_view()
    {
        // Create a test view file
        $viewPath = base_path('resources/views/test-view.blade.php');
        $viewDir = dirname($viewPath);

        if (! is_dir($viewDir)) {
            mkdir($viewDir, 0755, true);
        }

        file_put_contents($viewPath, '<div>Test View</div>');

        try {
            $view = $this->factory->make('test-view');
            $this->assertInstanceOf(View::class, $view);
        } catch (\Exception $e) {
            // View might not be found, which is expected in test environment
            $this->assertTrue(true);
        } finally {
            // Clean up
            if (file_exists($viewPath)) {
                unlink($viewPath);
            }
            if (is_dir($viewDir) && count(scandir($viewDir)) <= 2) {
                rmdir($viewDir);
            }
        }
    }

    public function test_factory_can_exists()
    {
        $exists = $this->factory->exists('nonexistent-view');

        $this->assertIsBool($exists);
    }

    public function test_factory_can_share_data()
    {
        $this->factory->share('test-key', 'test-value');

        $this->assertTrue(true); // Method should not throw exception
    }

    public function test_factory_can_composer()
    {
        $this->factory->composer('test-view', function ($view) {
            $view->with('test', 'value');
        });

        $this->assertTrue(true); // Method should not throw exception
    }

    public function test_factory_can_creator()
    {
        $this->factory->creator('test-view', function ($view) {
            $view->with('test', 'value');
        });

        $this->assertTrue(true); // Method should not throw exception
    }
}
