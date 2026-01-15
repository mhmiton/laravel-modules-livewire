<?php

namespace Mhmiton\LaravelModulesLivewire\Support\Livewire;

use Livewire\Finder\Finder;

class ModuleLivewireFinder extends Finder
{
    public function parseNamespaceAndName($name): array
    {
        if (substr_count($name, '::') > 1) {
            // Sort namespaces by length descending to match longest first (e.g. 'auth::pages' before 'auth')
            $namespaces = array_keys($this->viewNamespaces);
            // Sort by length specificially to be safe
            usort($namespaces, fn($a, $b) => strlen($b) - strlen($a));

            foreach ($namespaces as $namespace) {
                if (str_starts_with($name, $namespace . '::')) {
                    $componentName = substr($name, strlen($namespace . '::'));
                    return [$namespace, $componentName];
                }
            }
        }

        return parent::parseNamespaceAndName($name);
    }

    public function copyStateFrom(Finder $original)
    {
        $reflection = new \ReflectionClass($original);
        foreach (['classLocations', 'viewLocations', 'classNamespaces', 'viewNamespaces', 'classComponents', 'viewComponents'] as $prop) {
            if ($reflection->hasProperty($prop)) {
                $property = $reflection->getProperty($prop);
                $property->setAccessible(true);
                $this->{$prop} = $property->getValue($original);
            }
        }
    }
}
