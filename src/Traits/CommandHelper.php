<?php

namespace Mhmiton\LaravelModulesLivewire\Traits;

use Illuminate\Support\Facades\File;
use Livewire\Features\SupportConsoleCommands\Commands\MakeCommand;
use Nwidart\Modules\Traits\PathNamespace;

trait CommandHelper
{
    use ModuleTrait, PathNamespace;

    /**
     * Determine if the 'force' option has been specified and is set to true.
     *
     * @return bool Returns true if the 'force' option is enabled; otherwise, false.
     */
    protected function isForce(): bool
    {
        return $this->option('force') === true;
    }

    /**
     * Determines if the 'inline' option has been set to true.
     *
     * @return bool Returns true if the 'inline' option is enabled; otherwise, false.
     */
    protected function isInline(): bool
    {
        return $this->option('inline') === true;
    }

    /**
     * Ensures that the specified directory exists.
     *
     * If the given path is a file, it determines the directory name from the path.
     * If the directory does not exist, it creates it with the specified permissions.
     *
     * @param  string  $path  The file or directory path to check or create.
     */
    protected function ensureDirectoryExists(string $path): void
    {
        $dir = File::extension($path) ? dirname($path) : $path;

        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0777, $recursive = true, $force = true);
        }
    }

    protected function checkClassNameValid()
    {
        if (! $this->isClassNameValid($name = $this->component->class->name)) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS! </> 😳 \n");
            $this->line("<fg=red;options=bold>Class is invalid:</> {$name}");

            return false;
        }

        return true;
    }

    protected function checkReservedClassName()
    {
        if ($this->isReservedClassName($name = $this->component->class->name)) {
            $this->line("<options=bold,reverse;fg=red> WHOOPS! </> 😳 \n");
            $this->line("<fg=red;options=bold>Class is reserved:</> {$name}");

            return false;
        }

        return true;
    }

    protected function isClassNameValid($name)
    {
        return (new MakeCommand)->isClassNameValid($name);
    }

    protected function isReservedClassName($name)
    {
        return (new MakeCommand)->isReservedClassName($name);
    }
}
