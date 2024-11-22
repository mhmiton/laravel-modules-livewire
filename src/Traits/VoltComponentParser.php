<?php

namespace Mhmiton\LaravelModulesLivewire\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Mhmiton\LaravelModulesLivewire\Support\Decomposer;
use Mhmiton\LaravelModulesLivewire\Support\ModuleVoltComponentRegistry;

trait VoltComponentParser
{
    use CommandHelper;

    protected $component;

    protected $module;

    protected $directories;

    protected function parser()
    {
        $checkDependencies = Decomposer::checkDependencies(['livewire/volt']);

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
        $viewInfo = $this->getViewInfo();

        $stubInfo = $this->getStubInfo();

        return (object) [
            'view' => $viewInfo,
            'stub' => $stubInfo,
        ];
    }

    protected function getViewInfo()
    {
        $moduleVoltResourceViewDir = $this->getModuleVoltResourceViewDir();

        $path = $this->directories
            ->map([Str::class, 'kebab'])
            ->implode('/');

        $componentTag = $this->getComponentTag();

        return (object) [
            'dir' => $moduleVoltResourceViewDir,
            'path' => $path,
            'folder' => Str::after($moduleVoltResourceViewDir, 'views/'),
            'file' => $moduleVoltResourceViewDir.'/'.$path.'.blade.php',
            'name' => strtr($path, ['/' => '.']),
            'tag' => $componentTag,
        ];
    }

    protected function getModuleVoltComponentData()
    {
        return (new ModuleVoltComponentRegistry())->getModuleComponentData(
            $this->getModuleLowerName()
        );
    }

    protected function getModuleVoltResourceViewDir()
    {
        $moduleVoltComponentData = $this->getModuleVoltComponentData();

        $viewPathFull = data_get($moduleVoltComponentData, 'view_path_full');

        $viewNamespaces = data_get($moduleVoltComponentData, 'volt_view_namespaces');

        $allowedViewNamespace = $viewNamespaces[0] ?? null;

        if ($optionView = $this->option('view')) {
            $allowedViewNamespace = $optionView;

            $isOptionViewExistInViewNamespaces = in_array($optionView, $viewNamespaces);

            if (! $isOptionViewExistInViewNamespaces) {
                $this->line("<options=bold,reverse;fg=red> WHOOPS! </> 😳 \n");
                $this->line("<fg=red;options=bold>The '{$optionView}' view is not registered in the 'volt_view_namespaces' config.</>");

                $allowedViewNamespace = $this->choice('Plese choose one of the registered view namespace:', $viewNamespaces, ($viewNamespaces[0] ?? null));

                $this->input->setOption('view', $allowedViewNamespace);
            }
        }

        $moduleVoltResourceViewDir = $viewPathFull.'/'.$allowedViewNamespace;

        return $moduleVoltResourceViewDir;
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

        $classStubName = 'volt-component-class.stub';

        $classStub = File::exists($stubDir.$classStubName)
            ? $stubDir.$classStubName
            : $defaultStubDir.$classStubName;

        $functionalStubName = 'volt-component.stub';

        $functionalStub = File::exists($stubDir.$functionalStubName)
            ? $stubDir.$functionalStubName
            : $defaultStubDir.$functionalStubName;

        return (object) [
            'dir' => $stubDir,
            'class' => $classStub,
            'functional' => $functionalStub,
        ];
    }

    protected function getViewContents()
    {
        $componentType = $this->getComponentType();

        return preg_replace(
            '/\[quote\]/',
            $this->getComponentQuote(),
            file_get_contents($this->component->stub->$componentType),
        );
    }

    protected function getViewName()
    {
        return $this->getModuleLowerName().'::'.$this->component->view->name;
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

    protected function getComponentType()
    {
        $componentType = $this->option('class') ? 'class' : 'functional';

        if (! $this->option('class') && ! $this->option('functional') && $this->alreadyUsingClasses()) {
            $componentType = 'class';
        }

        return $componentType;
    }

    protected function getComponentQuote()
    {
        return "The <code>{$this->getViewName()}</code> volt component is loaded from the ".($this->isCustomModule() ? 'custom ' : '')."<code>{$this->getModuleName()}</code> module.";
    }

    protected function getBasePath($path = null)
    {
        return strtr(base_path($path), ['\\' => '/']);
    }

    /**
     * Determine if the project is currently using class-based components.
     */
    protected function alreadyUsingClasses(): bool
    {
        $moduleVoltResourceViewDir = $this->getModuleVoltResourceViewDir();

        $files = collect(File::allFiles($moduleVoltResourceViewDir));

        foreach ($files as $file) {
            if ($file->getExtension() === 'php' && str_ends_with($file->getFilename(), '.blade.php')) {
                $content = File::get($file->getPathname());

                if (str_contains($content, 'use Livewire\Volt\Component') ||
                    str_contains($content, 'new class extends Component')) {
                    return true;
                }
            }
        }

        return false;
    }
}
