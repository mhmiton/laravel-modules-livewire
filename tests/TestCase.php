<?php

namespace Mhmiton\LaravelModulesLivewire\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mhmiton\LaravelModulesLivewire\Commands\LivewireMakeCommand;
use Mhmiton\LaravelModulesLivewire\Commands\LivewireMakeFormCommand;
use Mhmiton\LaravelModulesLivewire\Commands\VoltMakeCommand;
use Mhmiton\LaravelModulesLivewire\Tests\Traits\InitModule;

require_once __DIR__.'/Traits/InitModule.php';

// Determine parent class
if (class_exists(\Orchestra\Testbench\TestCase::class)) {
    class ParentTestCase extends \Orchestra\Testbench\TestCase
    {
        protected function getPackageProviders($app)
        {
            return [
                \Nwidart\Modules\LaravelModulesServiceProvider::class,
                \Mhmiton\LaravelModulesLivewire\LivewireComponentServiceProvider::class,
                \Livewire\LivewireServiceProvider::class,
            ];
        }

        protected function getEnvironmentSetUp($app)
        {
             $app['config']->set('app.key', 'base64:Hupx3yAySikrM2/edkZQNQHslgDWYfiBfCuSThJ5SK8=');
             $app['config']->set('cache.default', 'array');
             $app['config']->set('session.driver', 'array');
             $app['config']->set('queue.default', 'sync');
             $app['config']->set('database.default', 'testing');
             $app['config']->set('database.connections.testing', [
                 'driver' => 'sqlite',
                 'database' => ':memory:',
                 'prefix' => '',
             ]);

             $vendorPath = is_dir(__DIR__.'/../vendor') ? __DIR__.'/../vendor' : __DIR__.'/../../../../vendor';

             if (file_exists($vendorPath.'/nwidart/laravel-modules/config/config.php')) {
                 $modulesConfig = require $vendorPath.'/nwidart/laravel-modules/config/config.php';
                 $app['config']->set('modules', $modulesConfig);
             }

             if (file_exists($vendorPath.'/livewire/livewire/config/livewire.php')) {
                 $livewireConfig = require $vendorPath.'/livewire/livewire/config/livewire.php';
                 $app['config']->set('livewire', $livewireConfig);
             }

             if (file_exists(__DIR__.'/../config/modules-livewire.php')) {
                 $modulesLivewireConfig = require __DIR__.'/../config/modules-livewire.php';
                 $app['config']->set('modules-livewire', $modulesLivewireConfig);
             }
        }
    }
} else {
    // Modify to extend App TestCase
    class ParentTestCase extends \Tests\TestCase {}
}

class TestCase extends ParentTestCase
{
    use InitModule, RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('optimize:clear');

        // Register commands if they aren't registered by the provider
        // Assuming provider registers them? 
        // Mhmiton\LaravelModulesLivewire\LaravelModulesLivewireServiceProvider usually registers commands.
        // But TestCase was doing manual registration.
        // I'll keep manual registration to be safe.
        
        $kernel = $this->app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->registerCommand($this->app->make(LivewireMakeCommand::class));
        $kernel->registerCommand($this->app->make(LivewireMakeFormCommand::class));
        $kernel->registerCommand($this->app->make(VoltMakeCommand::class));

        $this->setUpModule();
    }
}
