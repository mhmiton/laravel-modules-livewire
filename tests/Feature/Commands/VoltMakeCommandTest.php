<?php

namespace Mhmiton\LaravelModulesLivewire\Tests\Feature\Commands;

use Illuminate\Support\Facades\File;
use Mhmiton\LaravelModulesLivewire\Tests\TestCase;

class VoltMakeCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create a test module directory structure
        $this->createTestModule();
    }

    protected function tearDown(): void
    {
        // Clean up test files
        $this->cleanupTestModule();

        parent::tearDown();
    }

    public function test_can_create_volt_component_with_dot_notation()
    {
        $this->artisan('module:make-volt', [
            'component' => 'volt.counter',
            'module' => 'Core',
        ])
            ->assertExitCode(0);
    }

    public function test_can_create_volt_component_with_slash_notation()
    {
        $this->artisan('module:make-volt', [
            'component' => 'volt/counter',
            'module' => 'Core',
        ])
            ->assertExitCode(0);
    }

    public function test_can_force_create_volt_component()
    {
        // Create the component first
        $this->artisan('module:make-volt', [
            'component' => 'volt.counter',
            'module' => 'Core',
        ])
            ->assertExitCode(0);

        // Try to create it again with force
        $this->artisan('module:make-volt', [
            'component' => 'volt.counter',
            'module' => 'Core',
            '--force' => true,
        ])
            ->assertExitCode(0);
    }

    public function test_cannot_create_volt_component_without_force_when_exists()
    {
        // Create the component first
        $this->artisan('module:make-volt', [
            'component' => 'volt.counter',
            'module' => 'Core',
        ])
            ->assertExitCode(0);

        // Try to create it again without force
        $this->artisan('module:make-volt', [
            'component' => 'volt.counter',
            'module' => 'Core',
        ])
            ->assertExitCode(0);
    }

    public function test_can_create_volt_component_with_custom_view_namespace()
    {
        $this->artisan('module:make-volt', [
            'component' => 'volt.counter',
            'module' => 'Core',
        ])
            ->assertExitCode(0);
    }

    public function test_can_create_volt_component_with_pages_view_namespace()
    {
        $this->artisan('module:make-volt', [
            'component' => 'pages.home',
            'module' => 'Core',
        ])
            ->assertExitCode(0);
    }

    public function test_can_create_class_based_volt_component()
    {
        $this->artisan('module:make-volt', [
            'component' => 'volt.counter',
            'module' => 'Core',
            '--class' => true,
        ])
            ->assertExitCode(0);
    }

    public function test_can_create_functional_volt_component()
    {
        $this->artisan('module:make-volt', [
            'component' => 'volt.counter',
            'module' => 'Core',
            '--functional' => true,
        ])
            ->assertExitCode(0);
    }

    public function test_can_create_volt_component_with_custom_stub()
    {
        // Create custom stub directory
        $stubPath = base_path('stubs/modules-livewire/custom');
        File::makeDirectory($stubPath, 0755, true, true);
        File::put($stubPath.'/volt-component.stub', '<div>Test Volt Component</div>');

        $this->artisan('module:make-volt', [
            'component' => 'volt.counter',
            'module' => 'Core',
            '--stub' => 'custom',
        ])
            ->assertExitCode(0);

        // Clean up
        File::deleteDirectory($stubPath);
    }

    public function test_validates_volt_component_name()
    {
        $this->artisan('module:make-volt', [
            'component' => '123Invalid',
            'module' => 'Core',
        ])
            ->assertExitCode(0);
    }

    protected function createTestModule()
    {
        $modulePath = base_path('modules/Core');

        // Create module directory structure
        File::makeDirectory($modulePath.'/resources/views/livewire', 0755, true, true);
        File::makeDirectory($modulePath.'/resources/views/pages', 0755, true, true);

        // Create module.json
        File::put($modulePath.'/module.json', json_encode([
            'name' => 'Core',
            'alias' => 'core',
            'namespace' => 'Modules\\Core',
        ]));
    }

    protected function cleanupTestModule()
    {
        $modulePath = base_path('modules/Core');
        if (File::exists($modulePath)) {
            File::deleteDirectory($modulePath);
        }
    }
}
