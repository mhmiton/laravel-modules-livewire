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

    protected $viewNamespace;

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

        $componentName = $this->argument('component');
        
        if (str_contains($componentName, '::')) {
            [$this->viewNamespace, $componentName] = explode('::', $componentName, 2);
        }

        $this->directories = collect(
            preg_split('/[.\/(\\\\)]+|::/', $componentName)
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

    public function isMfc()
    {
        return $this->option('mfc');
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
        $className = $this->directories->last();
        $componentTag = $this->getComponentTag();

        if ($this->isMfc()) {
            $viewDir = $this->getModuleLivewireViewDir();
            if ($this->viewNamespace) {
                $viewDir .= '/' . $this->viewNamespace;
            }

            $directories = $this->directories;
            $className = $directories->last();
            $shouldUseEmoji = config('livewire.make_command.emoji', true);
            $emojiPrefix = $shouldUseEmoji ? '⚡' : '';
            
            // Build path segments
            $segments = $directories->map([Str::class, 'kebab']);
            
            // For MFC, the last segment (directory) gets the emoji
            $lastSegment = $segments->pop();
            $path = $segments->implode('/');
            
            // Example: Pages/User -> pages/⚡user
            $directoryName = $emojiPrefix . $lastSegment;
            
            // Full directory path
            $fullDir = $viewDir . ($path ? '/'.$path : '') . '/' . $directoryName;
            
            // File name is just the class name (kebab) without emoji
            $fileName = Str::kebab($className);
            
            $classFile = $fullDir . '/' . $fileName . '.php';

            return (object) [
                'dir' => $fullDir,
                'path' => ($path ? $path.'/' : '') . $directoryName,
                'file' => $classFile,
                'namespace' => null,
                'name' => $className,
                'tag' => $componentTag,
            ];
        }

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
        if ($this->viewNamespace) {
            $moduleLivewireViewDir .= '/' . $this->viewNamespace;
        }
        
        $directories = $this->directories;
        $className = $directories->last();
        $shouldUseEmoji = config('livewire.make_command.emoji', true);
        $emojiPrefix = $shouldUseEmoji ? '⚡' : '';

        // Standard SFC Logic
        $path = $directories->map([Str::class, 'kebab'])->implode('/');
        
        if ($this->option('view')) {
            $path = strtr($this->option('view'), ['.' => '/']);
        } elseif (!$this->isMfc()) {
             // SFC: Prepend emoji to filename (last segment of path) if needed
             if ($shouldUseEmoji && ! Str::startsWith($className, '⚡')) {
                 $segments = $directories->map([Str::class, 'kebab']);
                 $last = $segments->pop();
                 $path = ($segments->isNotEmpty() ? $segments->implode('/').'/' : '') . $emojiPrefix . $last;
             }
        }
        
        $file = $moduleLivewireViewDir.'/'.$path.'.blade.php';

        if ($this->isMfc()) {
             $segments = $directories->map([Str::class, 'kebab']);
             $lastSegment = $segments->pop();
             $dirPath = $segments->implode('/');
             $directoryName = $emojiPrefix . $lastSegment;
             
             $fullRelPath = ($dirPath ? $dirPath.'/' : '') . $directoryName;
             
             $fileName = Str::kebab($className);
             
             $path = $fullRelPath;
             $file = $moduleLivewireViewDir . '/' . $fullRelPath . '/' . $fileName . '.blade.php';
        }

        return (object) [
            'dir' => $moduleLivewireViewDir,
            'path' => $path,
            'folder' => Str::after($moduleLivewireViewDir, 'views/'),
            'file' => $file,
            'name' => strtr($path, ['/' => '.']),
            'tag' => $this->getComponentTag(),
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
            'sfc' => File::exists($stubDir.'livewire-sfc.stub') ? $stubDir.'livewire-sfc.stub' : $defaultStubDir.'livewire-sfc.stub',
            'mfc_class' => File::exists($stubDir.'livewire-mfc-class.stub') ? $stubDir.'livewire-mfc-class.stub' : $defaultStubDir.'livewire-mfc-class.stub',
            'mfc_view' => File::exists($stubDir.'livewire-mfc-view.stub') ? $stubDir.'livewire-mfc-view.stub' : $defaultStubDir.'livewire-mfc-view.stub',
        ];
    }

    protected function getClassContents()
    {
        $template = file_get_contents($this->component->stub->class);

        if ($this->isInline()) {
            $template = preg_replace('/\[quote\]/', $this->getComponentQuote(), $template);
        }

        $template = preg_replace(
            ['/\[namespace\]/', '/\[class\]/', '/\[view\]/'],
            [$this->getClassNamespace(), $this->getClassName(), $this->getViewName()],
            $template,
        );

        if ($this->isMfc()) {
            $template = file_get_contents($this->component->stub->mfc_class);
        }

        return $template;
    }

    protected function getViewContents()
    {
        if ($this->isSfc()) {
           return preg_replace(
                '/\[quote\]/',
                $this->getComponentQuote(),
                file_get_contents($this->component->stub->sfc),
            );
        }

        if ($this->isMfc()) {
            return preg_replace(
                '/\[quote\]/',
                $this->getComponentQuote(),
                file_get_contents($this->component->stub->mfc_view),
            );
        }

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
        $prefix = $this->getModuleLowerName().'::';
        if ($this->viewNamespace) {
            $prefix .= $this->viewNamespace . '.'; // Internal view name uses dot? Or ::?
            // View names for loading usually use dot or ::. 
            // If we use :: in tag, we should match.
            // But getViewName is used for class->render() view('xxx'). 
            // View finder works with :: for namespaces.
            // If viewNamespace is 'pages', it's registered as 'auth::pages'.
            // So view name inside that namespace is just 'component'.
            // So 'auth::pages::component'.
            // But here we return the string.
            // If we return 'auth::pages.component', it looks in 'auth' NS, 'pages.component' file.
            // If we return 'auth::pages::component', it looks in 'auth::pages' NS.
            // So we should return 'auth::pages::component'.
            
             return $this->getModuleLowerName().'::' . $this->viewNamespace . '::' . $this->component->view->name;
        }

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

        $namespacePart = $this->viewNamespace ? $this->viewNamespace . '::' : '';
        
        $tag = "<livewire:{$this->getModuleLowerName()}::{$namespacePart}{$directoryAsView} />";

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

    public function isSfc()
    {
        return $this->option('sfc');
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
