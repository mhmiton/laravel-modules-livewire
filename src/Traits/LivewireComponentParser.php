<?php

namespace Mhmiton\LaravelModulesLivewire\Traits;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Mhmiton\LaravelModulesLivewire\Support\Decomposer;

trait LivewireComponentParser
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
            preg_split('/[.\/(\\\\)]+|::/', $this->argument('component'))
        )->map([Str::class, 'studly']);

        $this->component = $this->getComponent();

        return $this;
    }

    protected function getComponent()
    {
        if ($this->isCbc()) {
            $componentData['class'] = $this->getClassInfo();
        }

        $componentData['view'] = $this->getViewInfo();

        $componentData['stub'] = $this->getStubInfo();

        return (object) $componentData;
    }

    protected function getClassInfo()
    {
        $modulePath = $this->getModulePath(true);

        $moduleLivewireNamespace = $this->getModuleLivewireNamespace();

        $classDir = (string) Str::of($modulePath)
            ->append('/'.$moduleLivewireNamespace)
            ->replace(['\\'], '/');

        $classPath = $this->directories->implode('/');

        $namespace = $this->getNamespace($classPath);

        $classData['dir'] = $classDir;
        $classData['path'] = $classPath;
        $classData['file'] = $classDir.'/'.$classPath.'.php';
        $classData['namespace'] = $namespace;
        $classData['name'] = $this->directories->last();
        $classData['tag'] = $this->getComponentTag();
        $classData['tag_name'] = $this->getComponentTagName();

        return (object) $classData;
    }

    protected function getViewInfo()
    {
        $moduleLivewireViewDir = $this->getModuleLivewireViewDir();

        $directories = $this->directories->map([Str::class, 'kebab']);

        $path = $directories->implode('/');

        if ($this->isCbc() && $this->option('view')) {
            $path = strtr($this->option('view'), ['.' => '/']);
        }

        if ($this->isSfc() || $this->isMfc()) {
            $emoji = $this->option('emoji') || config('livewire.make_command.emoji', true) ? '⚡' : '';

            if ($this->option('emoji') === 'false') {
                $emoji = '';
            }

            $path = $directories->count() > 1
                ? Str::replaceLast('/', "/{$emoji}", $path)
                : "{$emoji}$path";
        }

        // MFC - Initialize emoji in component folder and the last directory is the component name
        if ($this->isMfc()) {
            $componentName = $emoji
                ? str_replace(['⚡', '⚡︎', '⚡️'], '', $directories->last())
                : $directories->last();

            $file = $moduleLivewireViewDir.'/'.$path.'/'.$componentName.'.blade.php';

            $viewData['mfc_files'] = [
                'class' => $moduleLivewireViewDir.'/'.$path.'/'.$componentName.'.php',
                'view' => $file,
                'test' => $moduleLivewireViewDir.'/'.$path.'/'.$componentName.'.test.php',
                'js' => $moduleLivewireViewDir.'/'.$path.'/'.$componentName.'.js',
                // 'css' => $moduleLivewireViewDir.'/'.$path.'/'.$componentName.'.css',
                // 'css_global' => $moduleLivewireViewDir.'/'.$path.'.global.css',
            ];
        }

        $viewData['dir'] = $moduleLivewireViewDir;
        $viewData['path'] = $path;
        $viewData['folder'] = Str::after($moduleLivewireViewDir, 'views/');
        $viewData['file'] = $file ?? $moduleLivewireViewDir.'/'.$path.'.blade.php';
        $viewData['name'] = strtr($path, ['/' => '.', '⚡' => '']);
        $viewData['tag'] = $this->getComponentTag();
        $viewData['tag_name'] = $this->getComponentTagName();

        return (object) $viewData;
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

        $stubData['dir'] = $stubDir;

        if ($this->isCbc()) {
            $stubData['class'] = File::exists($stubDir.$classStubName)
                ? $stubDir.$classStubName
                : $defaultStubDir.$classStubName;

            $stubData['view'] = File::exists($stubDir.'livewire.view.stub')
                ? $stubDir.'livewire.view.stub'
                : $defaultStubDir.'livewire.view.stub';
        }

        if ($this->isSfc()) {
            $stubData['view'] = File::exists($stubDir.'livewire-sfc.stub')
                ? $stubDir.'livewire-sfc.stub'
                : $defaultStubDir.'livewire-sfc.stub';
        }

        if ($this->isMfc()) {
            $stubData['view'] = File::exists($stubDir.'livewire-mfc-view.stub')
                ? $stubDir.'livewire-mfc-view.stub'
                : $defaultStubDir.'livewire-mfc-view.stub';

            $stubData['mfc_stubs']['class'] = File::exists($stubDir.'livewire-mfc-class.stub')
                ? $stubDir.'livewire-mfc-class.stub'
                : $defaultStubDir.'livewire-mfc-class.stub';

            $stubData['mfc_stubs']['view'] = $stubData['view'];

            $stubData['mfc_stubs']['test'] = File::exists($stubDir.'livewire-mfc-test.stub')
                ? $stubDir.'livewire-mfc-test.stub'
                : $defaultStubDir.'livewire-mfc-test.stub';

            $stubData['mfc_stubs']['js'] = File::exists($stubDir.'livewire-mfc-js.stub')
                ? $stubDir.'livewire-mfc-js.stub'
                : $defaultStubDir.'livewire-mfc-js.stub';
        }

        return (object) $stubData;
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
        return (string) Str::of($this->component->view->file)
            ->after($this->getBasePath().'/')
            ->replace('//', '/');
    }

    protected function getComponentTagName()
    {
        $directoryAsView = $this->directories
            ->map([Str::class, 'kebab'])
            ->implode('.');

        return (string) Str::of("{$this->getModuleLowerName()}::{$directoryAsView}")
            ->replaceLast('.index', '')
            ->replace('⚡', '');
    }

    protected function getComponentTag()
    {
        return "<livewire:{$this->getComponentTagName()} />";
    }

    protected function getComponentQuote()
    {
        return "The <code>{$this->getComponentTagName()}</code> ".$this->determineComponentType().' component is loaded from the '.($this->isCustomModule() ? 'custom ' : '')."<code>{$this->getModuleName()}</code> module.";
    }

    protected function getBasePath($path = null)
    {
        return strtr(base_path($path), ['\\' => '/']);
    }

    /**
     * Get the value of a command option.
     *
     * @param  string|null  $key
     * @return string|array|bool|null
     */
    public function option($key = null)
    {
        try {
            return parent::option($key);
        } catch (Exception $e) {
            return null;
        }
    }
}
