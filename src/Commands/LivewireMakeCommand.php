<?php

namespace Mhmiton\LaravelModulesLivewire\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Mhmiton\LaravelModulesLivewire\Traits\ComponentParser;

class LivewireMakeCommand extends Command
{
    use ComponentParser;

    protected $signature = 'module:make-livewire {component} {module} {--view=} {--force} {--inline} {--stub=} {--custom}';

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
        if (! $this->parser()) {
            return false;
        }

        if (! $this->checkClassNameValid()) {
            return false;
        }

        if (! $this->checkReservedClassName()) {
            return false;
        }

        $class = $this->createClass();

        $view = $this->createView();

        if ($class || $view) {
            $this->line("<options=bold,reverse;fg=green> COMPONENT CREATED </> 🤙\n");

            $class && $this->line("<options=bold;fg=green>CLASS:</> {$this->getClassSourcePath()}");

            $view && $this->line("<options=bold;fg=green>VIEW:</>  {$this->getViewSourcePath()}");

            $class && $this->line("<options=bold;fg=green>TAG:</> {$class->tag}");
        }

        return false;
    }

    protected function createClass()
    {
        $classFile = $this->component->class->file;

        if (File::exists($classFile) && ! $this->isForce()) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>Class already exists:</> {$this->getClassSourcePath()}");

            return false;
        }

        $this->ensureDirectoryExists($classFile);

        File::put($classFile, $this->getClassContents());

        return $this->component->class;
    }

    protected function createView()
    {
        if ($this->isInline()) {
            return false;
        }

        $viewFile = $this->component->view->file;

        if (File::exists($viewFile) && ! $this->isForce()) {
            $this->line("<fg=red;options=bold>View already exists:</> {$this->getViewSourcePath()}");

            return false;
        }

        $this->ensureDirectoryExists($viewFile);

        File::put($viewFile, $this->getViewContents());

        return $this->component->view;
    }
}
