<?php

namespace Mhmiton\LaravelModulesLivewire\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\File;
use Mhmiton\LaravelModulesLivewire\Traits\LivewireComponentParser;

class LivewireMakeCommand extends Command implements PromptsForMissingInput
{
    use LivewireComponentParser;

    protected $signature = 'module:make-livewire
        {component : The name of the component}
        {module : The module to generate the class in}
        {--f|force : Overwrite existing files?}
        {--i|inline : Create inline component}
        {--view= : The view file name}
        {--stub= : Use a custom stub}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Livewire Component.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $checks = [
            $this->parser(),
            $this->checkClassNameValid(),
            $this->checkReservedClassName(),
        ];

        if (in_array(false, $checks)) {
            return Command::FAILURE;
        }

        $class = $this->createClass();

        $view = $this->createView();

        if ($class || $view) {
            $this->line("<options=bold,reverse;fg=green> COMPONENT CREATED </> 🤙\n");

            $class && $this->line("<options=bold;fg=green>CLASS:</> {$this->getClassSourcePath()}");

            $view && $this->line("<options=bold;fg=green>VIEW:</>  {$this->getViewSourcePath()}");

            $class && $this->line("<options=bold;fg=green>TAG:</> {$class->tag}");
        }

        return Command::SUCCESS;
    }

    protected function createClass()
    {
        $file = $this->component->class->file;

        if (File::exists($file) && ! $this->isForce()) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>Class already exists:</> {$this->getClassSourcePath()}");

            return false;
        }

        $this->ensureDirectoryExists($file);

        File::put($file, $this->getClassContents());

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
