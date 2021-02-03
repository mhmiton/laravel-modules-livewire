<?php

namespace Mhmiton\LaravelModulesLivewire\Support;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class Decomposer
{
    protected $dependencies = ['livewire/livewire', 'nwidart/laravel-modules'];

    public static function getComposerData()
    {
        try {
            $composer = (new Filesystem)->get(base_path('composer.json'));
            return collect( json_decode($composer, true) );
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    public static function getPackage($packageName)
    {
        $packages = self::getComposerData();

        $packageFile = base_path("/vendor/{$packageName}/composer.json");

        if (! file_exists($packageFile)) return null;

        $version = $packages->get('require')[$packageName]
            ?? $packages->get('require-dev')[$packageName]
            ?? null;

        return $version ? (object) ['name' => $packageName, 'version' => $version] : null;
    }

    public static function hasPackage($packageName)
    {
        if (is_array($packageName)) return self::hasPackages($packageName);

        return self::getPackage($packageName) ? true : false;
    }

    public static function hasPackages($packageNames = [])
    {
        $packages = $packageNames ?? (new static)->dependencies;

        foreach ($packages as $v) {
            if (! self::getPackage($v)) {
                return false;
                break;
            }
        }

        return true;
    }

    public static function checkDependencies($packageNames = null)
    {
        $packages = $packageNames ?? (new static)->dependencies;
        
        $type = 'success';
        
        $output = '';

        if (! self::hasPackages($packages) || ! class_exists('Livewire') || ! class_exists('Module')) {
            $type = 'error';

            $output .= "\n<options=bold,reverse;fg=red> WHOOPS! </> 😳 \n\n";

            foreach ($packages as $v) {
                if (! self::hasPackage($v)) {
                    $name = Str::of($v)->after('/')->studly();
                    
                    $output .= "<fg=red;options=bold>{$name} not found!</> \n";
                    
                    $output .= "<fg=green;options=bold>Install the {$name} package - composer require {$v}</> \n\n";
                }
            }
        }

        return (object) ['type' => $type, 'message' => $output];
    }
}