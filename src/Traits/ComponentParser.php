<?php

namespace Mhmiton\LaravelModulesLivewire\Traits;

use Illuminate\Support\Facades\File;
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

        $stubInfo = $this->getStubInfo();

        return (object) [
            'class' => $classInfo,
            'view' => $viewInfo,
            'stub' => $stubInfo,
        ];
    }

    protected function getClassInfo()
    {
        $modulePath = $this->getModulePath();

        $moduleLivewireNamespace = $this->getModuleLivewireNamespace();

        $classDir = (string) Str::of($modulePath)
            ->append('/'.$moduleLivewireNamespace)
            ->replace(['\\'], '/');

        $classPath = $this->directories->implode('/');

        $namespace = $this->getNamespace($classPath);

        $className = $this->directories->last();

        $componentTag = $this->getComponentTag();

        return (object) [
            'dir' => $classDir,
            'path' => $classPath,
            'file' => $classDir.'/'.$classPath.'.php',
            'namespace' => $namespace,
            'name' => $className,
            'tag' => $componentTag,
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
            'file' => $moduleLivewireViewDir.'/'.$path.'.blade.php',
            'name' => strtr($path, ['/' => '.']),
        ];
    }

    protected function getStubInfo()
    {
        $defaultStubDir = __DIR__.'/../Commands/stubs/';

        $stubDir = File::isDirectory($publishedStubDir = base_path('stubs/modules-livewire/'))
            ? $publishedStubDir
            : $defaultStubDir;

        if ($this->option('stub')) {
            $customStubDir = Str::of(base_path('stubs/'))
                ->append($this->option('stub').'/')
                ->replace(['../', './'], '');

            $stubDir = File::isDirectory($customStubDir) ? $customStubDir : $stubDir;
        }

        $classStubName = $this->isInline() ? 'livewire.inline.stub' : 'livewire.stub';

        $classStub = File::exists($stubDir.$classStubName)
            ? $stubDir.$classStubName
            : $defaultStubDir.$classStubName;

        $viewStub = File::exists($stubDir.'livewire.view.stub')
            ? $stubDir.'livewire.view.stub'
            : $defaultStubDir.'livewire.view.stub';

        return (object) [
            'dir' => $stubDir,
            'class' => $classStub,
            'view' => $viewStub,
        ];
    }

    protected function getClassContents()
    {
        $template = file_get_contents($this->component->stub->class);

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
            file_get_contents($this->component->stub->view),
        );
    }

    protected function getClassSourcePath()
    {
        return Str::after($this->component->class->file, $this->getBasePath().'/');
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
        return $this->getModuleLowerName().'::'.$this->component->view->folder.'.'.$this->component->view->name;
    }

    protected function getViewSourcePath()
    {
        return Str::after($this->component->view->file, $this->getBasePath().'/');
    }

    protected function getComponentTag()
    {
        $directoryAsView = $this->directories
            ->map([Str::class, 'kebab'])
            ->implode('.');

        $tag = "<livewire:{$this->getModuleLowerName()}::{$directoryAsView} />";

        $tagWithOutIndex = Str::replaceLast('.index', '', $tag);

        return $tagWithOutIndex;
    }

    protected function getComponentQuote()
    {
        return "The <code>{$this->getClassName()}</code> livewire component is loaded from the ".($this->isCustomModule() ? 'custom ' : '')."<code>{$this->getModuleName()}</code> module.";
    }

    protected function getBasePath($path = null)
    {
        return strtr(base_path($path), ['\\' => '/']);
    }
}
