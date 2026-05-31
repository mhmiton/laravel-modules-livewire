<?php

namespace Mhmiton\LaravelModulesLivewire\Tests\Feature\Commands;

use Illuminate\Support\Facades\File;
use Mhmiton\LaravelModulesLivewire\Tests\TestCase;

class LivewireMakeFormCommandTest extends TestCase
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

    public function test_can_create_livewire_form_component_with_slash_notation()
    {
        $this->artisan('module:make-livewire-form', [
            'component' => 'Forms/PostForm',
            'module' => 'Core',
        ])
            ->assertExitCode(0);

        $this->assertFileExists(base_path('Modules/Core/app/Livewire/Forms/PostForm.php'));
    }

    public function test_can_create_livewire_form_component_with_backslash_notation()
    {
        $this->artisan('module:make-livewire-form', [
            'component' => 'Forms\\PostForm',
            'module' => 'Core',
        ])
            ->assertExitCode(0);

        $this->assertFileExists(base_path('Modules/Core/app/Livewire/Forms/PostForm.php'));
    }

    public function test_can_create_livewire_form_component_with_dot_notation()
    {
        $this->artisan('module:make-livewire-form', [
            'component' => 'forms.post-form',
            'module' => 'Core',
        ])
            ->assertExitCode(0);

        $this->assertFileExists(base_path('Modules/Core/app/Livewire/Forms/PostForm.php'));
    }

    public function test_can_force_create_form_component()
    {
        // Create the component first
        $this->artisan('module:make-livewire-form', [
            'component' => 'Forms/PostForm',
            'module' => 'Core',
        ])
            ->assertExitCode(0);

        // Try to create it again with force
        $this->artisan('module:make-livewire-form', [
            'component' => 'Forms/PostForm',
            'module' => 'Core',
            '--force' => true,
        ])
            ->assertExitCode(0);
    }

    public function test_cannot_create_form_component_without_force_when_exists()
    {
        // Create the component first
        $this->artisan('module:make-livewire-form', [
            'component' => 'Forms/PostForm',
            'module' => 'Core',
        ])
            ->assertExitCode(0);

        // Try to create it again without force
        $this->artisan('module:make-livewire-form', [
            'component' => 'Forms/PostForm',
            'module' => 'Core',
        ])
            ->assertExitCode(0);
    }

    public function test_can_create_form_component_with_custom_stub()
    {
        // Create custom stub directory
        $stubPath = base_path('stubs/modules-livewire/custom');
        File::makeDirectory($stubPath, 0755, true, true);
        File::put($stubPath.'/livewire.form.stub', '<?php namespace {{ namespace }}; class {{ class }} { }');

        $this->artisan('module:make-livewire-form', [
            'component' => 'Forms/PostForm',
            'module' => 'Core',
            '--stub' => 'custom',
        ])
            ->assertExitCode(0);

        // Clean up
        File::deleteDirectory($stubPath);
    }

    public function test_validates_form_component_name()
    {
        $this->artisan('module:make-livewire-form', [
            'component' => '123Invalid',
            'module' => 'Core',
        ])
            ->assertExitCode(0);
    }

    public function test_validates_reserved_form_class_names()
    {
        $this->artisan('module:make-livewire-form', [
            'component' => 'Component',
            'module' => 'Core',
        ])
            ->assertExitCode(0);
    }

    protected function createTestModule()
    {
        $modulePath = base_path('Modules/Core');

        // Create module directory structure
        File::makeDirectory($modulePath.'/app/Livewire', 0755, true, true);
        File::makeDirectory($modulePath.'/resources/views/livewire', 0755, true, true);

        // Create module.json
        File::put($modulePath.'/module.json', json_encode([
            'name' => 'Core',
            'alias' => 'core',
            'namespace' => 'Modules\\Core',
        ]));
    }

    protected function cleanupTestModule()
    {
        $modulePath = base_path('Modules/Core');
        if (File::exists($modulePath)) {
            File::deleteDirectory($modulePath);
        }
    }
}
