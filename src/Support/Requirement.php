<?php

namespace Mhmiton\LaravelModulesLivewire\Support;

class Requirement
{
    public function checkDependencies()
    {
        $type = 'success';
        $output = '';

        if (! class_exists('Livewire') || ! class_exists('Module')) {
            $type = 'error';

            $output .= "\n<options=bold,reverse;fg=red> WHOOPS! </> 😳 \n\n";
            
            if (! class_exists('Livewire')) {
                $output .= "<fg=red;options=bold>Livewire not found!</> \n";
                $output .= "<fg=green;options=bold>Install The Livewire Package - composer require livewire/livewire</> \n\n";
            }

            if (! class_exists('Module')) {
                $output .= "<fg=red;options=bold>Larave Modules not found!</> \n";
                $output .= "<fg=green;options=bold>Install The Laravel Modules Package - composer require nwidart/laravel-modules</> \n\n";
            }
        }

        return (object) ['type' => $type, 'message' => $output];
    }
}