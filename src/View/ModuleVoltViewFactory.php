<?php

namespace Mhmiton\LaravelModulesLivewire\View;

use Illuminate\View\Factory;
use Illuminate\Support\Str;
use Mhmiton\LaravelModulesLivewire\Support\ModuleVoltComponentRegistry;

class ModuleVoltViewFactory extends Factory
{
    /**
     * The \Livewire\Volt\Component::class render method retruns Facades\View::make with "volt-livewire::" alias.
     * This makes support module view namespace.
     */
    public function make($view, $data = [], $mergeData = [])
    {
        $isVoltLivewireView = Str::startsWith($view, 'volt-livewire::');

        $isModuleView = count(explode('::', $view)) == 3;

        if (! $isVoltLivewireView || ! $isModuleView) {
            return parent::make($view, $data, $mergeData);
        }

        $moduleName = Str::of($view)
            ->beforeLast('::')
            ->replace(['volt-livewire::'], '')
            ->toString();

        $moduleComponentData = (new ModuleVoltComponentRegistry())->getModuleComponentData($moduleName);

        $moduleVoltViewNamespaces = data_get($moduleComponentData, 'volt_view_namespaces');

        $isModulePathExists = data_get($moduleComponentData, 'is_path_exists') ? true : false;

        if (! $isModulePathExists) {
            return parent::make($view, $data, $mergeData);
        }

        foreach ($moduleVoltViewNamespaces as $moduleVoltViewNamespace) {
            $viewWithoutAlias = Str::afterLast($view, '::');

            $moduleVoltView = "{$moduleName}::{$moduleVoltViewNamespace}.{$viewWithoutAlias}";

            if ($this->exists($moduleVoltView)) {
                return parent::make($moduleVoltView, $data, $mergeData);
            }
        }

        return parent::make($view, $data, $mergeData);
    }
}
