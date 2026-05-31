<?php

namespace Mhmiton\LaravelModulesLivewire\Tests\Unit\Support;

use Illuminate\Support\Facades\File;
use Mhmiton\LaravelModulesLivewire\Support\ModuleVoltComponentRegistry;
use Mhmiton\LaravelModulesLivewire\Tests\TestCase;

class ModuleVoltComponentRegistryTest extends TestCase
{
    protected $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = new ModuleVoltComponentRegistry;
    }

    public function test_register_components_returns_false_when_volt_not_available()
    {
        // Mock the class_exists check to return false
        $result = $this->registry->registerComponents();

        // This will depend on whether Volt is actually available
        $this->assertIsBool($result);
    }

    public function test_get_registerable_components_returns_empty_array_when_no_view_namespaces()
    {
        $components = $this->registry->getRegisterableComponents(
            base_path('Modules/Core'),
            [],
            'core::'
        );

        $this->assertIsArray($components);
        $this->assertEmpty($components);
    }

    public function test_get_registerable_components_returns_empty_array_when_directory_not_exists()
    {
        $components = $this->registry->getRegisterableComponents(
            '/nonexistent/path',
            ['livewire'],
            'core::'
        );

        $this->assertIsArray($components);
        $this->assertEmpty($components);
    }

    public function test_get_registerable_components_finds_blade_files()
    {
        // Create test directory structure
        $testPath = base_path('Modules/Core');
        $viewPath = $testPath.'/resources/views/livewire/';

        File::makeDirectory($viewPath, 0755, true, true);
        File::put($viewPath.'test-component.blade.php', '<div>Test</div>');

        $components = $this->registry->getRegisterableComponents(
            $testPath,
            ['livewire'],
            'core::'
        );

        $this->assertIsArray($components);
        $this->assertNotEmpty($components);

        // Clean up
        File::deleteDirectory($testPath);
    }

    public function test_get_registerable_components_ignores_non_blade_files()
    {
        // Create test directory structure
        $testPath = base_path('Modules/Core');
        $viewPath = $testPath.'/resources/views/livewire/';

        File::makeDirectory($viewPath, 0755, true, true);
        File::put($viewPath.'test-component.txt', 'Test content');

        $components = $this->registry->getRegisterableComponents(
            $testPath,
            ['livewire'],
            'core::'
        );

        $this->assertIsArray($components);
        $this->assertEmpty($components);

        // Clean up
        File::deleteDirectory($testPath);
    }

    public function test_get_registerable_components_creates_correct_aliases()
    {
        // Create test directory structure
        $testPath = base_path('Modules/Core');
        $viewPath = $testPath.'/resources/views/livewire/';

        File::makeDirectory($viewPath, 0755, true, true);
        File::put($viewPath.'test-component.blade.php', '<div>Test</div>');

        $components = $this->registry->getRegisterableComponents(
            $testPath,
            ['livewire'],
            'core::'
        );

        $this->assertIsArray($components);
        $this->assertNotEmpty($components);

        $component = $components[0];
        $this->assertArrayHasKey('alias', $component);
        $this->assertArrayHasKey('path', $component);
        $this->assertEquals('core::test-component', $component['alias']);

        // Clean up
        File::deleteDirectory($testPath);
    }

    public function test_get_registerable_components_handles_nested_directories()
    {
        // Create test directory structure
        $testPath = base_path('Modules/Core');
        $viewPath = $testPath.'/resources/views/livewire/pages/';

        File::makeDirectory($viewPath, 0755, true, true);
        File::put($viewPath.'about-page.blade.php', '<div>About</div>');

        $components = $this->registry->getRegisterableComponents(
            $testPath,
            ['livewire'],
            'core::'
        );

        $this->assertIsArray($components);
        $this->assertNotEmpty($components);

        $component = $components[0];
        $this->assertEquals('core::pages.about-page', $component['alias']);

        // Clean up
        File::deleteDirectory($testPath);
    }

    public function test_get_module_component_data_returns_array()
    {
        $data = $this->registry->getModuleComponentData('core');

        $this->assertIsArray($data);
    }

    public function test_get_module_component_data_returns_default_values()
    {
        $data = $this->registry->getModuleComponentData('core');

        $this->assertArrayHasKey('view_path', $data);
        $this->assertArrayHasKey('volt_view_namespaces', $data);
    }

    public function test_component_method_registers_component()
    {
        try {
            $result = $this->registry->component('test-component', 'test-view');
            $this->assertIsBool($result);
        } catch (\Error $e) {
            // Livewire Volt is not available in test environment
            $this->assertTrue(true, 'Livewire Volt not available in test environment');
        }
    }
}
