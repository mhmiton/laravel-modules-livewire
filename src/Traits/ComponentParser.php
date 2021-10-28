<?php

namespace Mhmiton\LaravelModulesLivewire\Traits;

use Illuminate\Support\Str;
use Mhmiton\LaravelModulesLivewire\Support\Decomposer;

trait ComponentParser
{
    use CommandHelper;

    protected $component;

    protected $module;

    protected $directories;

    protected function parser()
    {
        $checkDependencies = Decomposer::checkDependencies(
            $this->isCustomModule() ? ['livewire/livewire'] : null
        );

        if ($checkDependencies->type == 'error') {
            $this->line($checkDependencies->message);

            return false;
        }

        if (! $module = $this->getModule()) {
            return false;
        }

        $this->module = $module;

        $this->directories = collect(
            preg_split('/[.\/(\\\\)]+/', $this->argument('component'))
        )->map([Str::class, 'studly']);

        $this->component = $this->getComponent();

        return $this;
    }

    protected function getComponent()
    {
        $classInfo = $this->getClassInfo();

        $viewInfo = $this->getViewInfo();

        return (object) [
            'class' => $classInfo,
            'view' => $viewInfo,
        ];
    }

    protected function getClassInfo()
    {
        $modulePath = $this->getModulePath();

        $moduleLivewireNamespace = $this->getModuleLivewireNamespace();

        $classDir = (string) Str::of($modulePath)
            ->append('/' . $moduleLivewireNamespace)
            ->replace(['\\'], '/');

        $classPath = $this->directories->implode('/');

        $namespace = $this->getNamespace($classPath);

        $className = $this->directories->last();

        $directoryAsView = $this->directories
            ->map([Str::class, 'kebab'])
            ->implode('.');

        return (object) [
            'dir' => $classDir,
            'path' => $classPath,
            'file' => $classDir . '/' . $classPath . '.php',
            'namespace' => $namespace,
            'name' => $className,
            'tag' => "<livewire:{$this->getModuleLowerName()}::{$directoryAsView} />",
        ];
    }

    protected function getViewInfo()
    {
        $moduleLivewireViewDir = $this->getModuleLivewireViewDir();

        $path = $this->directories
            ->map([Str::class, 'kebab'])
            ->implode('/');

        if ($this->option('view')) {
            $path = strtr($this->option('view'), ['.' => '/']);
        }

        return (object) [
            'dir' => $moduleLivewireViewDir,
            'path' => $path,
            'folder' => Str::after($moduleLivewireViewDir, 'views/'),
            'file' => $moduleLivewireViewDir . '/' . $path . '.blade.php',
            'name' => strtr($path, ['/' => '.']),
        ];
    }

    protected function getClassContents()
    {
        $stubPath = __DIR__ . '/../Commands/stubs/' . ($this->isInline() ? 'livewire.inline.stub' : 'livewire.stub');

        $template = file_get_contents($stubPath);

        if ($this->isInline()) {
            $template = preg_replace('/\[quote\]/', $this->getComponentQuote(), $template);
        }

        return preg_replace(
            ['/\[namespace\]/', '/\[class\]/', '/\[view\]/'],
            [$this->getClassNamespace(), $this->getClassName(), $this->getViewName()],
            $template,
        );
    }

    protected function getViewContents()
    {
        return preg_replace(
            '/\[quote\]/',
            $this->getComponentQuote(),
            file_get_contents(__DIR__ . '/../Commands/stubs/livewire.view.stub'),
        );
    }

    protected function getClassSourcePath()
    {
        return Str::after($this->component->class->file, $this->getBasePath() . '/');
    }

    protected function getClassNamespace()
    {
        return $this->component->class->namespace;
    }

    protected function getClassName()
    {
        return $this->component->class->name;
    }

    protected function getViewName()
    {
        return $this->getModuleLowerName() . '::' . $this->component->view->folder . '.' . $this->component->view->name;
    }

    protected function getViewSourcePath()
    {
        return Str::after($this->component->view->file, $this->getBasePath() . '/');
    }

    protected function getComponentQuote()
    {
        return "The <code>{$this->getClassName()}</code> livewire component is loaded from the " . ($this->isCustomModule() ? 'custom' : '') . " <code>{$this->getModuleName()}</code> module.";
    }

    protected function getBasePath($path = null)
    {
        return strtr(base_path($path), ['\\' => '/']);
    }
}
