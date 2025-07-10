<?php

namespace Mhmiton\LaravelModulesLivewire\Traits;

use Illuminate\Support\Facades\File;
use Nwidart\Modules\Helpers\Path;
use Nwidart\Modules\Laravel\Module;
use Nwidart\Modules\Traits\PathNamespace;

trait ModuleTrait
{
    use CustomModuleTrait, PathNamespace;

    /**
     * The module instance or name as a string.
     */
    protected Module|string $module;

    protected function getModuleNamespace()
    {
        return $this->isCustomModule()
            ? config("modules-livewire.custom_modules.{$this->module}.module_namespace", $this->module)
            : config('modules.namespace', 'Modules');
    }

    /**
     * Retrieves the file system path for the current module, optionally appending a subpath.
     *
     * If the module is a custom module, the path is retrieved from the configuration.
     * Otherwise, it uses the module's default path.
     *
     * @param  string|null  $path  Optional subpath to append to the module path.
     * @return string The full path to the module and the specified subpath.
     */
    protected function getModulePath(?string $path = null): string
    {
        $module_path = $this->isCustomModule()
            ? config("modules-livewire.custom_modules.{$this->module}.path")
            : $this->module->getPath();

        return $this->path($module_path.($path ? '/'.$this->path($path) : ''));
    }

    /**
     * Retrieves the module based on the provided 'module' argument.
     *
     * If a custom module is specified, it checks for its existence and directory path.
     * - Outputs error messages if the custom module or its path is not found.
     * - Returns the module name for custom modules if found.
     *
     * For standard modules, attempts to retrieve the module using the `module()` helper.
     * - Outputs error messages if the module is not found.
     * - Returns the module instance if found.
     *
     * @return mixed Returns the module name (string) for custom modules, the module instance for standard modules, or null if not found.
     */
    protected function getModule()
    {
        $name = $this->argument('module');

        if ($this->isCustomModule()) {
            $module = $this->getCustomModule();

            $path = $module['path'] ?? '';

            if (! $module || ! File::isDirectory($path)) {
                $this->line("<options=bold,reverse;fg=red> WHOOPS! </> 😳 \n");

                $path && $this->line("<fg=red;options=bold>The custom {$name} module not found in this path - {$path}.</>");

                ! $path && $this->line("<fg=red;options=bold>The custom {$name} module not found.</>");

                return null;
            }

            return $name;
        }

        if (! $module = module($name, true)) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS! </> 😳 \n");
            $this->line("<fg=red;options=bold>The {$name} module not found.</>");

            return null;
        }

        $this->module = $module;

        return $module;
    }

    /**
     * Retrieves the name of the module.
     *
     * If the module is a custom module, it returns the module property directly.
     * Otherwise, it calls the getName() method on the module object.
     *
     * @return string The name of the module.
     */
    protected function getModuleName(): string
    {
        return $this->isCustomModule() ? $this->module : $this->module->getName();
    }

    /**
     * Retrieves the lowercased name of the current module.
     *
     * If the module is a custom module, it attempts to fetch the lowercased name from the configuration.
     * If not found in the configuration, it defaults to the lowercased value of the module property.
     * For standard modules, it uses the module's getLowerName() method.
     *
     * @return string The lowercased module name.
     */
    protected function getModuleLowerName(): string
    {
        return $this->isCustomModule()
            ? config("modules-livewire.custom_modules.{$this->module}.name_lower", strtolower($this->module))
            : $this->module->getLowerName();
    }

    /**
     * Get the Livewire path for the current module.
     *
     * Determines the Livewire namespace or path based on the module configuration.
     * If the module is a custom module, it retrieves the custom namespace from the configuration.
     * Otherwise, it converts the default Livewire namespace to a path using the module's app_path method.
     *
     * @return string The Livewire namespace or path for the module.
     */
    protected function getModuleLivewirePath(): string
    {
        $moduleLivewireNamespace = config('livewire.class_namespace', 'App\\Livewire');

        if ($this->isCustomModule()) {
            return config("modules-livewire.custom_modules.{$this->module}.namespace", $moduleLivewireNamespace);
        }

        // Convert the `livewire.class_namespace` to path
        return $this->module->app_path($moduleLivewireNamespace);
    }

    /**
     * @deprecated use getModuleLivewirePath() instead.
     */
    protected function getModuleLivewireNamespace()
    {
        return $this->getModuleLivewirePath();
    }

    protected function getNamespace($classPath)
    {
        $namespace = $this->isCustomModule()
            ? $this->getModuleNamespace().'\\'.$this->getModuleLivewirePath()
            : $this->module_namespace($this->module->getName(), $this->getModuleLivewirePath());

        return $this->namespace("{$namespace}\\{$classPath}");
    }

    protected function getModuleLivewireViewDir()
    {
        $livewire_view = Path::relative(config('livewire.view_path', 'resources/views/livewire'));

        if ($this->isCustomModule()) {
            $livewire_view = config("modules-livewire.custom_modules.{$this->module}.view", $livewire_view);
        }

        return $this->getModulePath($livewire_view);
    }

    protected function getModuleResourceViewDir()
    {
        $views = config('modules.paths.generator.views.path', 'resources/views');

        if ($this->isCustomModule()) {
            $views = config("modules-livewire.custom_modules.{$this->module}.views_path", $views);
        }

        return $this->getModulePath($views);
    }
}
