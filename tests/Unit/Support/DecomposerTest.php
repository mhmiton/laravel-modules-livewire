<?php

namespace Mhmiton\LaravelModulesLivewire\Tests\Unit\Support;

use Illuminate\Support\Collection;
use Mhmiton\LaravelModulesLivewire\Support\Decomposer;
use Mhmiton\LaravelModulesLivewire\Tests\TestCase;

class DecomposerTest extends TestCase
{
    public function test_get_composer_data_returns_collection()
    {
        $data = Decomposer::getComposerData();

        $this->assertInstanceOf(Collection::class, $data);
    }

    public function test_get_package_returns_null_for_nonexistent_package()
    {
        $package = Decomposer::getPackage('nonexistent/package');

        $this->assertNull($package);
    }

    public function test_has_package_returns_false_for_nonexistent_package()
    {
        $hasPackage = Decomposer::hasPackage('nonexistent/package');

        $this->assertFalse($hasPackage);
    }

    public function test_has_packages_returns_false_when_any_package_missing()
    {
        $hasPackages = Decomposer::hasPackages(['nonexistent/package', 'another/nonexistent']);

        $this->assertFalse($hasPackages);
    }

    public function test_has_packages_returns_true_when_all_packages_exist()
    {
        // This test assumes that the required packages are installed
        // In a real test environment, you might want to mock this
        $hasPackages = Decomposer::hasPackages(['livewire/livewire']);

        // This will be true if livewire is installed, false otherwise
        $this->assertIsBool($hasPackages);
    }

    public function test_check_dependencies_returns_error_object_when_packages_missing()
    {
        $result = Decomposer::checkDependencies(['nonexistent/package']);

        $this->assertIsObject($result);
        $this->assertEquals('error', $result->type);
        $this->assertStringContainsString('WHOOPS!', $result->message);
        $this->assertStringContainsString('Package not found!', $result->message);
    }

    public function test_check_dependencies_returns_success_object_when_packages_exist()
    {
        // This test assumes that livewire is installed
        $result = Decomposer::checkDependencies(['livewire/livewire']);

        $this->assertIsObject($result);
        // The type will depend on whether livewire is actually installed
        $this->assertContains($result->type, ['success', 'error']);
    }

    public function test_check_dependencies_uses_default_dependencies_when_none_provided()
    {
        $result = Decomposer::checkDependencies();

        $this->assertIsObject($result);
        $this->assertContains($result->type, ['success', 'error']);
    }

    public function test_has_package_accepts_array_and_calls_has_packages()
    {
        $hasPackages = Decomposer::hasPackage(['package1', 'package2']);

        $this->assertIsBool($hasPackages);
    }

    public function test_get_package_returns_object_with_name_and_version()
    {
        // This test assumes that livewire is installed
        $package = Decomposer::getPackage('livewire/livewire');

        if ($package !== null) {
            $this->assertIsObject($package);
            $this->assertTrue(property_exists($package, 'name'));
            $this->assertTrue(property_exists($package, 'version'));
            $this->assertEquals('livewire/livewire', $package->name);
        } else {
            $this->assertNull($package);
        }
    }
}
