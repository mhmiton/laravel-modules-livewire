<?php

namespace Mhmiton\LaravelModulesLivewire\Support;

use Illuminate\Support\Str;
use Livewire\Livewire;

class ModuleVoltComponentRegistry
{
    public function registerComponents($options = [])
    {
        if (! class_exists(\Livewire\Volt\Volt::class)) {
            return false;
        }

        $path = data_get($options, 'path');

        $aliasPrefix = data_get($options, 'aliasPrefix');

        $namespace = data_get($options, 'namespace');

        $viewNamespaces = collect(\Arr::wrap(data_get($options, 'view_namespaces')))->filter()->all();

        // $this->mountModuleVoltComponents(Str::before($aliasPrefix, '::'));

        $registerableComponents = $this->getRegisterableComponents($path, $viewNamespaces, $aliasPrefix);

        collect($registerableComponents)
            ->each(function ($registerableComponent) use ($namespace) {
                $alias = data_get($registerableComponent, 'alias');

                $path = data_get($registerableComponent, 'path');

                // check if livewire class exists by alias

                // Alias To Class
                $componentClassNameWithoutNamespace = Str::of($alias)
                    ->after("counter::")
                    ->explode('.')
                    ->map([Str::class, 'studly'])
                    ->implode('\\');

                $componentClass = $namespace.'\\'.$componentClassNameWithoutNamespace;

                if (class_exists($componentClass)) {
                    return;
                }

                $this->component($alias, $path);
            });
    }

    public function getRegisterableComponents($path, $viewNamespaces = [], $aliasPrefix = null)
    {
        $moduleComponentData = $this->getModuleComponentData(Str::before($aliasPrefix, '::'));

        $registerableComponents = collect($viewNamespaces)
            ->map(function ($viewNamespace) use ($path, $aliasPrefix, $moduleComponentData) {
                $viewPath = data_get($moduleComponentData, 'view_path').'/'.$viewNamespace.'/';

                $fullViewPath = $path.'/'.$viewPath;

                if (! \File::isDirectory($fullViewPath)) {
                    return [];
                }

                $fileToComponents = collect(\File::allFiles($fullViewPath))
                    ->filter(fn($file) => str_ends_with($file->getFilename(), '.blade.php'))
                    ->map(function ($file) use ($aliasPrefix, $viewPath) {
                        $view = (string) Str::of($file->getPathname())
                            ->afterLast($viewPath)
                            ->replace(['/', '.blade.php'], ['.', ''])
                            ->explode('.')
                            ->map([Str::class, 'kebab'])
                            ->implode('.');

                        $alias = $aliasPrefix.$view;

                        return [
                            'alias' => $alias,
                            'path' => $file->getPathname(),
                        ];
                    })
                    ->values()
                    ->all();

                return $fileToComponents;
            })
            ->collapse()
            ->all();

        return $registerableComponents;
    }

    public function getModuleComponentData($moduleName = null)
    {
        $modulePath = $moduleName ? \Module::getModulePath($moduleName) : null;

        $moduleResourceViewPath = config('modules.paths.generator.views.path', 'resources/views');

        $moduleVoltViewNamespaces = collect(
            \Arr::wrap(config('modules-livewire.volt_view_namespace', ['livewire', 'pages']))
        )->filter()->all();

        // If module path not found, then check custom module path
        if (! \File::isDirectory($modulePath)) {
            $customModule = collect(config('modules-livewire.custom_modules', []))
                ->where('name_lower', $moduleName)
                ->first();

            $modulePath = data_get($customModule, 'path') ? data_get($customModule, 'path').'/' : null;

            $moduleResourceViewPath = data_get($customModule, 'views_path') ?? 'resources/views';

            $moduleVoltViewNamespaces = collect(
                \Arr::wrap($customModule['volt_view_namespaces'] ?? ['livewire', 'pages'])
            )->filter()->all();
        }

        $moduleComponentData = [
            'name' => $moduleName,
            'path' => $modulePath,
            'view_path' => $moduleResourceViewPath,
            'view_path_full' => $modulePath
                ? strtr($modulePath.'/'.$moduleResourceViewPath, ['//' => '/'])
                : $moduleResourceViewPath,
            'volt_view_namespaces' => $moduleVoltViewNamespaces,
            'is_path_exists' => \File::isDirectory($modulePath),
            'is_custom_module' => $customModule ?? false,
        ];

        return $moduleComponentData;
    }

    public function mountModuleVoltComponents($moduleName = null)
    {
        $moduleComponentData = $this->getModuleComponentData($moduleName);

        $mountPaths = collect(data_get($moduleComponentData, 'volt_view_namespaces', []))
            ->map(fn ($viewNamespace) => data_get($moduleComponentData, 'view_path_full').'/'.$viewNamespace)
            ->all();

        \Livewire\Volt\Volt::mount($mountPaths);
    }

    public function component($alias, $path)
    {
        $voltComponentRegistry = new \Livewire\Volt\ComponentFactory(
            new \Livewire\Volt\MountedDirectories()
        );

        $componentClass = $voltComponentRegistry->make($alias, $path);

        Livewire::component($alias, $componentClass);
    }
}
