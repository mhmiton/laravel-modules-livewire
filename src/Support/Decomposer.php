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
            $composer = (new Filesystem())->get(base_path('composer.lock'));

            return collect(data_get(json_decode($composer, true), 'packages'));
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    public static function getPackage($packageName)
    {
        $packages = self::getComposerData();

        if (! \File::isDirectory(base_path("/vendor/{$packageName}"))) {
            return null;
        }

        $version = $packages->firstWhere('name', $packageName)['version'] ?? null;

        return (object) ['name' => $packageName, 'version' => \Str::after($version, 'v')];
    }

    public static function hasPackage($packageName)
    {
        if (is_array($packageName)) {
            return self::hasPackages($packageName);
        }

        return self::getPackage($packageName) ? true : false;
    }

    public static function hasPackages($packageNames = [])
    {
        $packages = $packageNames ?? (new static())->dependencies;

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
        $packages = $packageNames ?? (new static())->dependencies;

        $type = 'success';

        $output = '';

        if (! self::hasPackages($packages)) {
            $type = 'error';

            $output .= "\n<options=bold,reverse;fg=red> WHOOPS! </> 😳 \n";

            foreach ($packages as $package) {
                if (! self::hasPackage($package)) {
                    $name = Str::of($package)->after('/')->studly();

                    $output .= "\n<fg=red;options=bold>{$name} not found!</> \n";

                    $output .= "<fg=green;options=bold>Install the {$name} package - composer require {$package}</> \n";
                }
            }
        }

        return (object) ['type' => $type, 'message' => $output];
    }
}
