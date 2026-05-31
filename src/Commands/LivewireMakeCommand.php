<?php

namespace Mhmiton\LaravelModulesLivewire\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\File;
use Mhmiton\LaravelModulesLivewire\Traits\LivewireComponentParser;

class LivewireMakeCommand extends Command implements PromptsForMissingInput
{
    use LivewireComponentParser;

    protected $signature = 'module:make-livewire {component} {module} {--sfc} {--mfc} {--class} {--force} {--inline} {--view=} {--emoji=} {--test} {--js} {--stub=}';

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

        if ($this->isSfc()) {
            return $this->createSingleFileComponent();
        }

        if ($this->isMfc()) {
            return $this->createMultiFileComponent();
        }

        if ($this->isCbc()) {
            return $this->createClassBasedComponent();
        }

        return false;
    }

    protected function createSingleFileComponent()
    {
        $viewFile = $this->component->view->file;

        if (File::exists($viewFile) && ! $this->isForce()) {
            $this->line("<options=bold,reverse;fg=red> COMPONENT EXISTS - SFC </> 😳 \n");
            $this->line("<fg=red;options=bold>Component already exists:</> {$this->getViewSourcePath()}");

            return false;
        }

        $this->ensureDirectoryExists($viewFile);

        File::put($viewFile, $this->getViewContents());

        $this->line("<options=bold,reverse;fg=green> COMPONENT CREATED - SFC </> 🤙\n");

        $this->line("<options=bold;fg=green>VIEW:</>  {$this->getViewSourcePath()}");

        $this->line("<options=bold;fg=green>TAG:</> {$this->component->view->tag}");
    }

    protected function createMultiFileComponent()
    {
        $viewFile = $this->component->view->mfc_files['view'];

        if (File::exists($viewFile) && ! $this->isForce()) {
            $this->line("<options=bold,reverse;fg=red> COMPONENT EXISTS - MFC </> 😳 \n");
            $this->line("<fg=red;options=bold>Component already exists:</> {$this->getViewSourcePath()}");

            return false;
        }

        $this->ensureDirectoryExists($viewFile);

        $this->line("<options=bold,reverse;fg=green> COMPONENT CREATED - MFC </> 🤙\n");

        File::put(
            $this->component->view->mfc_files['class'],
            file_get_contents($this->component->stub->mfc_stubs['class'])
        );

        $this->line('<options=bold;fg=green>CLASS:</>  '.strtr($this->getViewSourcePath(), ['.blade.php' => '.php']));

        File::put(
            $viewFile,
            preg_replace(
                '/\[quote\]/',
                $this->getComponentQuote(),
                file_get_contents($this->component->stub->mfc_stubs['view']),
            )
        );

        $this->line("<options=bold;fg=green>VIEW:</>  {$this->getViewSourcePath()}");

        if ($this->option('test') || config('livewire.make_command.with.test')) {
            File::put(
                $this->component->view->mfc_files['test'],
                preg_replace(
                    '/\[component-name\]/',
                    $this->component->view->tag_name,
                    file_get_contents($this->component->stub->mfc_stubs['test']),
                )
            );

            $this->line('<options=bold;fg=green>TEST:</>  '.strtr($this->getViewSourcePath(), ['.blade.php' => '.test.php']));
        }

        if ($this->option('js') || config('livewire.make_command.with.js')) {
            File::put(
                $this->component->view->mfc_files['js'],
                file_get_contents($this->component->stub->mfc_stubs['js'])
            );

            $this->line('<options=bold;fg=green>JS:</>  '.strtr($this->getViewSourcePath(), ['.blade.php' => '.js']));
        }

        $this->line("<options=bold;fg=green>TAG:</> {$this->component->view->tag}");
    }

    protected function createClassBasedComponent()
    {
        $class = $this->createClass();

        $view = $this->createView();

        if ($class || $view) {
            $this->line("<options=bold,reverse;fg=green> COMPONENT CREATED - CLASS BASED </> 🤙\n");

            $class && $this->line("<options=bold;fg=green>CLASS:</> {$this->getClassSourcePath()}");

            $view && $this->line("<options=bold;fg=green>VIEW:</>  {$this->getViewSourcePath()}");

            $class && $this->line("<options=bold;fg=green>TAG:</> {$class->tag}");
        }
    }

    protected function createClass()
    {
        $classFile = $this->component->class->file;

        if (File::exists($classFile) && ! $this->isForce()) {
            $this->line("<options=bold,reverse;fg=red> COMPONENT EXISTS - CLASS BASED </> 😳 \n");
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
