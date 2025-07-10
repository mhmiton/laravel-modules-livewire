<?php

namespace Mhmiton\LaravelModulesLivewire\Traits;

use Illuminate\Support\Facades\File;

trait CustomModuleTrait
{
    /**
     * Determines if the specified module is a custom module.
     *
     * This method checks if the module path exists. If it does not,
     * it attempts to resolve the module as a custom module.
     *
     * @return bool Returns true if the module is a custom module, false otherwise.
     */
    protected function isCustomModule(): bool
    {
        $name = $this->argument('module');
        $module = module($name, true);
        $modulePath = $module ? $module->getPath() : null;

        // If module path not found, then check custom module path
        if (! File::isDirectory($modulePath)) {
            return (bool) $this->getCustomModule();
        }

        return false;
    }

    /**
     * Retrieves a custom module configuration.
     *
     * This method attempts to fetch a custom module configuration from the application's
     * configuration using the 'modules-livewire.custom_modules' key.
     *
     * @return mixed The custom module configuration if found, or null otherwise.
     */
    protected function getCustomModule()
    {
        $name = $this->argument('module');

        return config('modules-livewire.custom_modules.'.$name) ??
            collect(config('modules-livewire.custom_modules', []))
                ->where('name_lower', $name)
                ->first();
    }
}
