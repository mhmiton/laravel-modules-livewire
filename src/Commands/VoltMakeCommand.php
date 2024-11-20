<?php

namespace Mhmiton\LaravelModulesLivewire\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\File;
use Mhmiton\LaravelModulesLivewire\Traits\VoltComponentParser;

class VoltMakeCommand extends Command implements PromptsForMissingInput
{
    use VoltComponentParser;

    protected $component;

    protected $module;

    protected $directories;

    protected $signature = 'module:make-volt {component} {module} {--view=} {--class} {--functional} {--force} {--stub=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Livewire Volt Component.';

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

        $view = $this->createView();

        if ($view) {
            $this->line("<options=bold,reverse;fg=green> VOLT COMPONENT CREATED </> 🤙\n");

            $this->line("<options=bold;fg=green>VIEW:</>  {$this->getViewSourcePath()}");

            $this->line("<options=bold;fg=green>TAG:</> {$view->tag}");
        }

        return false;
    }

    protected function createView()
    {
        $viewFile = $this->component->view->file;

        if (File::exists($viewFile) && ! $this->isForce()) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS-IE-TOOTLES </> 😳 \n");
            $this->line("<fg=red;options=bold>View already exists:</> {$this->getViewSourcePath()}");

            return false;
        }

        $this->ensureDirectoryExists($viewFile);

        File::put($viewFile, $this->getViewContents());

        return $this->component->view;
    }
}
