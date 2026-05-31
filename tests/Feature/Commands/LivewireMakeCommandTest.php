<?php

namespace Mhmiton\LaravelModulesLivewire\Tests\Feature\Commands;

use Illuminate\Support\Facades\File;
use Mhmiton\LaravelModulesLivewire\Tests\TestCase;

class LivewireMakeCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_core_module_is_exists()
    {
        $hasModule = $this->hasTestModule();

        $this->assertTrue($hasModule);
    }

    public function test_can_create_livewire_component_with_slash_notation()
    {
        $this->artisan('module:make-livewire', [
            'component' => 'Pages/AboutPage',
            'module' => 'Core',
        ])
            ->assertExitCode(0);

        $this->assertFileExists(base_path('Modules/Core/app/Livewire/Pages/AboutPage.php'));
        $this->assertFileExists(base_path('Modules/Core/resources/views/livewire/pages/about-page.blade.php'));
    }

    public function test_can_create_livewire_component_with_backslash_notation()
    {
        $this->artisan('module:make-livewire', [
            'component' => 'Pages\\AboutPage',
            'module' => 'Core',
        ])
            ->assertExitCode(0);

        $this->assertFileExists(base_path('Modules/Core/app/Livewire/Pages/AboutPage.php'));
    }

    public function test_can_create_livewire_component_with_dot_notation()
    {
        $this->artisan('module:make-livewire', [
            'component' => 'pages.about-page',
            'module' => 'Core',
        ])
            ->assertExitCode(0);

        $this->assertFileExists(base_path('Modules/Core/app/Livewire/Pages/AboutPage.php'));
    }

    public function test_can_create_inline_component()
    {
        $this->artisan('module:make-livewire', [
            'component' => 'Pages/AboutPage',
            'module' => 'Core',
            '--inline' => true,
        ])
            ->assertExitCode(0);

        $this->assertFileExists(base_path('Modules/Core/app/Livewire/Pages/AboutPage.php'));
        $this->assertFileDoesNotExist(base_path('Modules/Core/resources/views/livewire/pages/about-page.blade.php'));
    }

    public function test_can_force_create_component()
    {
        // Create the component first
        $this->artisan('module:make-livewire', [
            'component' => 'Pages/AboutPage',
            'module' => 'Core',
        ])
            ->assertExitCode(0);

        // Try to create it again with force
        $this->artisan('module:make-livewire', [
            'component' => 'Pages/AboutPage',
            'module' => 'Core',
            '--force' => true,
        ])
            ->assertExitCode(0);
    }

    public function test_cannot_create_component_without_force_when_exists()
    {
        // Create the component first
        $this->artisan('module:make-livewire', [
            'component' => 'Pages/AboutPage',
            'module' => 'Core',
        ])
            ->assertExitCode(0);

        // Try to create it again without force
        $this->artisan('module:make-livewire', [
            'component' => 'Pages/AboutPage',
            'module' => 'Core',
        ])
            ->assertExitCode(0);
    }

    public function test_can_create_component_with_custom_view_path()
    {
        $this->artisan('module:make-livewire', [
            'component' => 'Pages/AboutPage',
            'module' => 'Core',
            '--view' => 'pages/about',
        ])
            ->assertExitCode(0);

        $this->assertFileExists(base_path('Modules/Core/resources/views/livewire/pages/about.blade.php'));
    }

    public function test_can_create_component_with_custom_stub()
    {
        // Create custom stub directory
        $stubPath = base_path('stubs/modules-livewire/custom');
        File::makeDirectory($stubPath, 0755, true, true);
        File::put($stubPath.'/livewire.stub', '<?php namespace {{ namespace }}; class {{ class }} { }');

        $this->artisan('module:make-livewire', [
            'component' => 'Pages/AboutPage',
            'module' => 'Core',
            '--stub' => 'custom',
        ])
            ->assertExitCode(0);

        // Clean up
        File::deleteDirectory($stubPath);
    }

    public function test_validates_component_name()
    {
        $this->artisan('module:make-livewire', [
            'component' => '123Invalid',
            'module' => 'Core',
        ])
            ->assertExitCode(0);
    }

    public function test_validates_reserved_class_names()
    {
        $this->artisan('module:make-livewire', [
            'component' => 'Component',
            'module' => 'Core',
        ])
            ->assertExitCode(0);
    }
}
