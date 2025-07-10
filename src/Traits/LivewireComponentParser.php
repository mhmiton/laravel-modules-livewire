<?php

namespace Mhmiton\LaravelModulesLivewire\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Nwidart\Modules\Helpers\Path;

trait LivewireComponentParser
{
    use CommandHelper;

    protected $component;

    protected $directories;

    protected $file;

    protected function parser(): self|bool
    {
        if (! $module = $this->getModule()) {
            return false;
        }

        $this->module = $module;

        $this->file = Path::studly($this->argument('component'));

        $this->directories = collect(preg_split('/[.\/(\\\\)]+/', Path::directory($this->argument('component'))))
            ->map([Str::class, 'studly']);

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
        $dir = $this->path($this->getModulePath($this->getModuleLivewirePath())); // todo: examine app/ path handling.
        $path = $this->directories->implode('/');
        $filename = Path::join($dir, $this->file);

        return (object) [
            'name' => Path::filename($this->file),
            'path' => $path,
            'namespace' => $this->getNamespace($path),
            'file' => "{$filename}.php",
            'dir' => $dir,
            'tag' => $this->getComponentTag(),
        ];
    }

    protected function view()
    {
        $dir = $this->getModuleLivewireViewDir();
        $path = $this->directories->map([Str::class, 'kebab'])->implode('/');
        if ($this->option('view')) {
            $path = strtr($this->option('view'), ['.' => '/']);
        }
        $file = Path::lower($this->file);
        $filename = Path::join($dir, $file);

        return (object) [
            'name' => strtr($file, ['/' => '.']),
            'path' => $path,
            'file' => "{$filename}.blade.php",
            'folder' => Str::after($dir, 'views/'),
            'dir' => $dir,
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
        $directoryAsView = Str::of($this->file)->explode('/')->map([Str::class, 'kebab'])->implode('.');
        $tag = "<livewire:{$this->getModuleLowerName()}::{$directoryAsView} />";

        return Str::replaceLast('.index', '', $tag);
    }

    protected function getComponentQuote()
    {
        $file = Str::of($this->file)->explode('/')->implode(' / ');

        return "<code>{$this->getModuleName()}".($this->isCustomModule() ? ' (custom)' : '').": {$file}</code>";
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
