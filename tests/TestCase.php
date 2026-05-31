<?php

namespace Mhmiton\LaravelModulesLivewire\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\LivewireServiceProvider;
use Mhmiton\LaravelModulesLivewire\Commands\LivewireMakeCommand;
use Mhmiton\LaravelModulesLivewire\Commands\LivewireMakeFormCommand;
use Mhmiton\LaravelModulesLivewire\Commands\VoltMakeCommand;
use Mhmiton\LaravelModulesLivewire\LaravelModulesLivewireServiceProvider;
use Mhmiton\LaravelModulesLivewire\Tests\Traits\InitModule;
use Nwidart\Modules\LaravelModulesServiceProvider;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

class TestCase extends TestbenchTestCase
{
    use InitModule, RefreshDatabase;

    /**
     * Automatically enables package discoveries.
     *
     * @var bool
     */
    protected $enablesPackageDiscoveries = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('optimize:clear');

        $kernel = $this->app->make('Illuminate\Contracts\Console\Kernel');
        $kernel->registerCommand($this->app->make(LivewireMakeCommand::class));
        $kernel->registerCommand($this->app->make(LivewireMakeFormCommand::class));
        $kernel->registerCommand($this->app->make(VoltMakeCommand::class));

        $this->setUpModule();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelModulesServiceProvider::class,
            LaravelModulesLivewireServiceProvider::class,
            LivewireServiceProvider::class,
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

        $modulesConfig = require __DIR__.'/../vendor/nwidart/laravel-modules/config/config.php';

        $app['config']->set('modules', $modulesConfig);

        $livewireConfig = require __DIR__.'/../vendor/livewire/livewire/config/livewire.php';

        $app['config']->set('livewire', $livewireConfig);

        $modulesLivewireConfig = require __DIR__.'/../config/modules-livewire.php';

        $app['config']->set('modules-livewire', $modulesLivewireConfig);
    }
}
