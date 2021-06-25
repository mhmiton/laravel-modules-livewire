<?php

namespace Mhmiton\LaravelModulesLivewire\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Mhmiton\LaravelModulesLivewire\Support\Stub;
use Mhmiton\LaravelModulesLivewire\Support\Decomposer;
use Mhmiton\LaravelModulesLivewire\Traits\ModuleCommandTrait;

class LivewireMakeCommand extends Command
{
    use ModuleCommandTrait;

    public $component;

    public $module;

    public $directories;

    protected $signature = 'module:make-livewire {component} {module} {--view=} {--force} {--inline}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Livewire Component.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $checkDependencies = Decomposer::checkDependencies();

        if ($checkDependencies->type == 'error') {
            $this->line($checkDependencies->message);
            return 0;
        }

        $this->component = $this->getComponent();

        if ($this->isReservedClassName($name = $this->component->class->name)) {
            $this->line("\n<options=bold,reverse;fg=red> WHOOPS! </> 😳 \n");
            $this->line("<fg=red;options=bold>Class is reserved:</> {$name} \n");
            return 0;
        }

        $force = $this->option('force');
        $inline = $this->option('inline');

        $class = $this->createClass($force, $inline);
        $view = $this->createView($force, $inline);

        if ($class || $view) {
            $this->line("\n <options=bold,reverse;fg=green> COMPONENT CREATED </> 🤙\n");
            $class && $this->line("<options=bold;fg=green>CLASS:</> {$this->component->class->path}");

            if (! $inline) {
                $view && $this->line("<options=bold;fg=green>VIEW:</>  {$this->component->view->path}");
            }
        }

        return 0;
    }

    protected function getComponent()
    {
        $module = $this->laravel['modules']->findOrFail($this->argument('module'));
        
        $this->module = $module;
        
        $this->directories = preg_split('/[.\/(\\\\)]+/', $this->argument('component'));

        $classInfo = $this->getClassInfo();

        $viewInfo = $this->getViewInfo();

        return (object) [
            'class' => $classInfo,
            'view' => $viewInfo
        ];
    }

    public function getClassInfo()
    {
        $modulesLivewireNamespace = config('modules-livewire.namespace', 'Http\\Livewire');

        $classDir = (string) Str::of($this->module->getPath())
                ->append('/' . $modulesLivewireNamespace)
                ->replace(['\\'], '/');

        $classPath = collect($this->directories)
            ->map([Str::class, 'studly'])
            ->implode('/');

        $namespaceAppend = Str::contains($classPath, '/') ? '/' . $classPath : '';

        $namespace = (string) Str::of($namespaceAppend)
            ->beforeLast('/')
            ->prepend( config('modules.namespace', 'Modules') . '\\' . $this->module->getName() . '\\' . $modulesLivewireNamespace )
            ->replace(['/'], ['\\']);

        $className = Str::studly(Arr::last($this->directories));

        return (object) [
            'dir' => $classDir,
            'path' => $classPath,
            'file' => $classDir . '/' . $classPath . '.php',
            'namespace' => $namespace,
            'name' => $className,
        ];
    }

    public function getViewInfo()
    {
        $modulePath = $this->module->getPath().'/';

        $viewDir = $modulePath . config('modules-livewire.view', 'Resources/views/livewire');

        $path = collect($this->directories)
            ->map([Str::class, 'kebab'])
            ->implode('/');

        if ($this->option('view')) $path = strtr($this->option('view'), ['.' => '/']);
        
        return (object) [
            'dir' => $viewDir,
            'path' => $path,
            'folder' => Str::after($viewDir, 'views/'),
            'file' => $viewDir . '/' . $path . '.blade.php',
            'name' => strtr($path, ['/' => '.']),
        ];
    }

    protected function createClass($force = false, $inline = false)
    {
        $classFile = $this->component->class->file;

        if (File::exists($classFile) && ! $force) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS </> 😳 \n");
            $this->line("<fg=red;options=bold>Class already exists:</> {$this->component->class->path}");
            return false;
        }

        $this->ensureDirectoryExists($classFile);

        File::put($classFile, $this->classContents($inline));

        return $classFile;
    }

    public function classContents($inline = false)
    {
        $stubName = $inline ? '/livewire.inline.stub' : '/livewire.stub';

        return (new Stub($stubName, [
            'NAMESPACE'  => $this->component->class->namespace,
            'CLASS'      => $this->component->class->name,
            'LOWER_NAME' => $this->module->getLowerName(),
            'VIEW_NAME'  => $this->component->view->folder . '.' . $this->component->view->name,
        ]))->render();
    }

    protected function createView($force = false, $inline = false)
    {
        if ($inline) return false;

        $viewFile = $this->component->view->file;

        if (File::exists($viewFile) && ! $force) {
            $this->line("<fg=red;options=bold>View already exists:</> {$this->component->view->path}");
            return false;
        }

        $this->ensureDirectoryExists($viewFile);

        File::put($viewFile, $this->viewContents());

        return $viewFile;
    }

    public function viewContents($inline = false)
    {
        return (new Stub('/livewire.view.stub'))->render();
    }

    protected function ensureDirectoryExists($path)
    {
        if (! File::isDirectory(dirname($path))) {
            File::makeDirectory(dirname($path), 0777, $recursive = true, $force = true);
        }
    }

    public function isReservedClassName($name)
    {
        return array_search($name, ['Parent', 'Component', 'Interface']) !== false;
    }
}
