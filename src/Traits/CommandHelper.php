<?php

namespace Mhmiton\LaravelModulesLivewire\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait CommandHelper
{
    protected function isCustomModule()
    {
        return $this->option('custom') === true;
    }

    protected function isForce()
    {
        return $this->option('force') === true;
    }

    protected function isInline()
    {
        return $this->option('inline') === true;
    }

    protected function ensureDirectoryExists($path)
    {
        if (! File::isDirectory(dirname($path))) {
            File::makeDirectory(dirname($path), 0777, $recursive = true, $force = true);
        }
    }

    protected function getModule()
    {
        $moduleName = $this->argument('module');

        if ($this->isCustomModule()) {
            $module = config("modules-livewire.custom_modules.{$moduleName}");

            $path = $module['path'] ?? '';

            if (! $module || ! File::isDirectory($path)) {
                $this->line("<options=bold,reverse;fg=red> WHOOPS! </> 😳 \n");
                $this->line("<fg=red;options=bold>The {$moduleName} module not found in this path - {$path}.</>");

                return null;
            }

            return $moduleName;
        }

        if (! $module = $this->laravel['modules']->find($moduleName)) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS! </> 😳 \n");
            $this->line("<fg=red;options=bold>The {$moduleName} module not found.</>");

            return null;
        }

        return $module;
    }

    protected function getModuleName()
    {
        return $this->isCustomModule()
            ? $this->module
            : $this->module->getName();
    }

    protected function getModuleLowerName()
    {
        return $this->isCustomModule()
            ? config("modules-livewire.custom_modules.{$this->module}.name_lower", strtolower($this->module))
            : $this->module->getLowerName();
    }

    protected function getModulePath()
    {
        $path = $this->isCustomModule()
            ? config("modules-livewire.custom_modules.{$this->module}.path")
            : $this->module->getPath();

        return strtr($path, ['\\' => '/']);
    }

    protected function getModuleNamespace()
    {
        return $this->isCustomModule()
            ? config("modules-livewire.custom_modules.{$this->module}.module_namespace", $this->module)
            : config('modules.namespace', 'Modules');
    }

    protected function getModuleLivewireNamespace()
    {
        $moduleLivewireNamespace = config('modules-livewire.namespace', 'Http\\Livewire');

        if ($this->isCustomModule()) {
            return config("modules-livewire.custom_modules.{$this->module}.namespace", $moduleLivewireNamespace);
        }

        return $moduleLivewireNamespace;
    }

    protected function getNamespace($classPath)
    {
        $classPath = Str::contains($classPath, '/') ? '/' . $classPath : '';

        $prefix = $this->isCustomModule()
            ? $this->getModuleNamespace() . '\\' . $this->getModuleLivewireNamespace()
            : $this->getModuleNamespace() . '\\' . $this->module->getName() . '\\' . $this->getModuleLivewireNamespace();

        return (string) Str::of($classPath)
            ->beforeLast('/')
            ->prepend($prefix)
            ->replace(['/'], ['\\']);
    }

    protected function getModuleLivewireViewDir()
    {
        $moduleLivewireViewDir = config('modules-livewire.view', 'Resources/views/livewire');

        if ($this->isCustomModule()) {
            $moduleLivewireViewDir = config("modules-livewire.custom_modules.{$this->module}.view", $moduleLivewireViewDir);
        }

        return $this->getModulePath() . '/' . $moduleLivewireViewDir;
    }

    protected function checkReservedName()
    {
        if ($this->isReservedName($name = $this->component->class->name)) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS! </> 😳 \n");
            $this->line("<fg=red;options=bold>Class is reserved:</> {$name}");

            return false;
        }

        return true;
    }

    protected function isReservedName($name)
    {
        return in_array(strtolower($name), $this->getReservedName());
    }

    protected function getReservedName()
    {
        return [
            'parent',
            'component',
            'interface',
            '__halt_compiler',
            'abstract',
            'and',
            'array',
            'as',
            'break',
            'callable',
            'case',
            'catch',
            'class',
            'clone',
            'const',
            'continue',
            'declare',
            'default',
            'die',
            'do',
            'echo',
            'else',
            'elseif',
            'empty',
            'enddeclare',
            'endfor',
            'endforeach',
            'endif',
            'endswitch',
            'endwhile',
            'eval',
            'exit',
            'extends',
            'final',
            'finally',
            'fn',
            'for',
            'foreach',
            'function',
            'global',
            'goto',
            'if',
            'implements',
            'include',
            'include_once',
            'instanceof',
            'insteadof',
            'interface',
            'isset',
            'list',
            'namespace',
            'new',
            'or',
            'print',
            'private',
            'protected',
            'public',
            'require',
            'require_once',
            'return',
            'static',
            'switch',
            'throw',
            'trait',
            'try',
            'unset',
            'use',
            'var',
            'while',
            'xor',
            'yield',
        ];
    }
}
