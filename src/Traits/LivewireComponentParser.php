<?php

namespace Mhmiton\LaravelModulesLivewire\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait LivewireComponentParser
{
    use CommandHelper;

    protected $component;

    protected $directories;

    protected function parser(): self|bool
    {
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
        return (object) [
            'class' => $this->class(),
            'view' => $this->view(),
            'stub' => $this->stub(),
        ];
    }

    protected function class()
    {
        $modulePath = $this->getModulePath(true);

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

    protected function view()
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

    protected function stub()
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
            [
                '/\[namespace\]/',
                '/\[class\]/',
                '/\[view\]/',
                '/\[layout\]/',
            ],
            [
                $this->getClassNamespace(),
                $this->getClassName(),
                $this->getViewName(),
                config('livewire.layout', 'components.layouts.app'),
            ],
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
        return Str::of($this->component->view->file)
            ->after($this->getBasePath().'/')
            ->replace('//', '/');
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

    /**
     * Retrieves the root path for the application.
     *
     * @param  string|null  $path  Optional subpath to append to the base path.
     * @return string The full base path for the application.
     */
    protected function getBasePath(?string $path = null): string
    {
        return strtr(base_path($path), ['\\' => '/']);
    }
}
