<?php

namespace Mhmiton\LaravelModulesLivewire\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Mhmiton\LaravelModulesLivewire\Support\Decomposer;
use Mhmiton\LaravelModulesLivewire\Support\ModuleVoltComponentRegistry;
use Mhmiton\LaravelModulesLivewire\View\ModuleVoltViewFactory;
use Nwidart\Modules\Facades\Module;

class LivewireComponentServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerModuleComponents();

        $this->registerCustomModuleComponents();

        $this->registerModuleVoltComponents();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    protected function registerModuleComponents()
    {
        if (Decomposer::checkDependencies()->type == 'error') {
            return false;
        }

        $modules = Module::toCollection();

        $modulesLivewireNamespace = config('modules-livewire.namespace', 'Livewire');

        $modules->each(function ($module) use ($modulesLivewireNamespace) {
            $directory = (string) Str::of($module->getAppPath())
                ->append('/'.$modulesLivewireNamespace)
                ->replace(['\\'], '/');

            $moduleNamespace = method_exists($module, 'getNamespace')
                ? $module->getNamespace()
                : config('modules.namespace', 'Modules');

            $namespace = $moduleNamespace.'\\'.$module->getName().'\\'.$modulesLivewireNamespace;

            $moduleLivewireViewPath = $module->getPath().'/'.config('modules-livewire.view', 'resources/views/livewire');

            // Register Locations
            Livewire::addLocation(
                viewPath: $moduleLivewireViewPath
            );

            // Register Namespaces
            Livewire::addNamespace(
                namespace: $module->getLowerName(),
                viewPath: $moduleLivewireViewPath
            );

            // Register a location for class-based components
            Livewire::addLocation(
                classNamespace: $namespace
            );

            // Register Class Based Components with Namespace
            Livewire::addNamespace(
                namespace: $module->getLowerName(),
                classNamespace: $namespace,
                classPath: $directory,
                classViewPath: $moduleLivewireViewPath
            );
        });
    }

    protected function registerCustomModuleComponents()
    {
        if (Decomposer::checkDependencies(['livewire/livewire'])->type == 'error') {
            return false;
        }

        $modules = collect(config('modules-livewire.custom_modules', []));

        $modules->each(function ($module, $moduleName) {
            $moduleAppPath = $module['path'].'/'.($module['app_path'] ?? null);

            $moduleLivewireNamespace = $module['namespace'] ?? config('modules-livewire.namespace', 'Livewire');

            $directory = (string) Str::of($moduleAppPath)
                ->append('/'.$moduleLivewireNamespace)
                ->replace(['\\'], '/');

            $namespace = ($module['module_namespace'] ?? $moduleName).'\\'.$moduleLivewireNamespace;

            $lowerName = $module['name_lower'] ?? strtolower($moduleName);

            $moduleLivewireViewPath = $module['path'].'/'.$module['view'];

            // Register Locations
            Livewire::addLocation(
                viewPath: $moduleLivewireViewPath
            );

            // Register Namespaces
            Livewire::addNamespace(
                namespace: $lowerName,
                viewPath: $moduleLivewireViewPath
            );

            // Register a location for class-based components
            Livewire::addLocation(
                classNamespace: $namespace
            );

            // Register Class Based Components with Namespace
            Livewire::addNamespace(
                namespace: $lowerName,
                classNamespace: $namespace,
                classPath: $directory,
                classViewPath: $moduleLivewireViewPath
            );
        });
    }

    public function registerModuleVoltComponents()
    {
        if (Decomposer::checkDependencies(['livewire/volt'])->type == 'error') {
            return false;
        }

        // Resolve Missing Module Volt Component
        Livewire::resolveMissingComponent(function (string $name) {
            return app(ModuleVoltComponentRegistry::class)->resolveComponent($name);
        });

        // Register ModuleVoltViewFactory
        $this->app->extend('view', function ($view, $app) {
            $factory = new ModuleVoltViewFactory(
                $app['view.engine.resolver'],
                $app['view.finder'],
                $app['events']
            );

            // Copy existing view paths
            foreach ($view->getFinder()->getPaths() as $path) {
                $factory->getFinder()->addLocation($path);
            }

            // Copy existing hint paths (this fixes the missing hint path issue)
            foreach ($view->getFinder()->getHints() as $namespace => $paths) {
                foreach ((array) $paths as $path) {
                    $factory->addNamespace($namespace, $path);
                }
            }

            $factory->setContainer($app);

            $factory->share('app', $app);

            return $factory;
        });

        \View::clearResolvedInstance('view');
    }
}
