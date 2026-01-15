<?php

namespace Mhmiton\LaravelModulesLivewire\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Livewire\Livewire;
use Nwidart\Modules\Facades\Module;

class ModuleLivewireComponentRegistry
{
    public function registerComponents($options = [])
    {
        $path = data_get($options, 'path');
        $aliasPrefix = data_get($options, 'aliasPrefix');
        $namespace = data_get($options, 'namespace');
        $viewNamespaces = collect(\Arr::wrap(data_get($options, 'view_namespaces')))->filter()->all();

        // Extract namespace from aliasPrefix (e.g., "test::" -> "test")
        $livewireNamespace = Str::before($aliasPrefix, '::');

        $moduleComponentData = $this->getModuleComponentData($livewireNamespace);
        
        // Default View Path: Modules/Test/resources/views/livewire
        $viewPath = data_get($moduleComponentData, 'view_path');
        if (! $viewPath) return;

        $livewireViewPath = $path . '/' . $viewPath . '/livewire';
        
        if (\File::isDirectory($livewireViewPath)) {
            app('livewire.finder')->addNamespace($livewireNamespace, $livewireViewPath);

            // Magic: Register subdirectories as nested namespaces (e.g. auth::pages)
            foreach (\File::directories($livewireViewPath) as $directory) {
                $basename = basename($directory);
                $nestedNamespace = $livewireNamespace . '::' . $basename;
                app('livewire.finder')->addNamespace($nestedNamespace, $directory);
            }
        }
    }
    
    // Helper methods mostly unused now or used for data retrieval for other parts?
    // KEEP getModuleComponentData.
    
    public function getModuleComponentData($moduleName = null)
    {
        $modulePath = $moduleName ? \Module::getModulePath($moduleName) : null;
        $moduleResourceViewPath = config('modules.paths.generator.views.path', 'resources/views');
        
        // Use volt_view_namespaces config as default for view-based components too?
        // Or adding a new config key `livewire_view_namespaces`?
        // Default to ['livewire', 'pages'] seems reasonable for v4.
        $moduleVoltViewNamespaces = collect(
            \Arr::wrap(config('modules-livewire.volt_view_namespace', ['livewire', 'pages']))
        )->filter()->all();

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

        return [
            'name' => $moduleName,
            'path' => $modulePath,
            'view_path' => $moduleResourceViewPath,
            'view_path_full' => $modulePath
                ? strtr($modulePath.'/'.$moduleResourceViewPath, ['//' => '/'])
                : $moduleResourceViewPath,
            'volt_view_namespaces' => $moduleVoltViewNamespaces,
        ];
    }
}
