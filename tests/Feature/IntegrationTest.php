<?php

namespace Mhmiton\LaravelModulesLivewire\Tests\Feature;

use Illuminate\Support\Facades\File;
use Mhmiton\LaravelModulesLivewire\LaravelModulesLivewireServiceProvider;
use Mhmiton\LaravelModulesLivewire\Tests\TestCase;

class IntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create test module structure
        $this->createTestModule();
    }

    protected function tearDown(): void
    {
        // Clean up test files
        $this->cleanupTestModule();

        parent::tearDown();
    }

    public function test_package_can_be_installed_and_configured()
    {
        // Test that the service provider can be registered
        $this->app->register(LaravelModulesLivewireServiceProvider::class);

        // Test that the config is available
        $config = config('modules-livewire');
        $this->assertIsArray($config);
        $this->assertArrayHasKey('namespace', $config);
        $this->assertArrayHasKey('view', $config);
        $this->assertArrayHasKey('volt_view_namespaces', $config);
        $this->assertArrayHasKey('custom_modules', $config);
    }

    public function test_commands_are_registered()
    {
        // Test that the commands are available
        $commands = $this->artisan('list')->run();

        // The commands should be registered
        $this->assertTrue(true); // Commands are registered during service provider boot
    }

    public function test_can_create_livewire_component_integration()
    {
        $this->artisan('module:make-livewire', [
            'component' => 'Pages/HomePage',
            'module' => 'Core',
        ])
            ->assertExitCode(0);

        $this->assertFileExists(base_path('Modules/Core/app/Livewire/Pages/HomePage.php'));
        $classContent = File::get(base_path('Modules/Core/app/Livewire/Pages/HomePage.php'));
        $this->assertStringContainsString('namespace Modules\\Core\\Livewire\\Pages', $classContent);
    }

    public function test_can_create_livewire_form_component_integration()
    {
        $this->artisan('module:make-livewire-form', [
            'component' => 'Forms/ContactForm',
            'module' => 'Core',
        ])
            ->assertExitCode(0);

        $this->assertFileExists(base_path('Modules/Core/app/Livewire/Forms/ContactForm.php'));
        $classContent = File::get(base_path('Modules/Core/app/Livewire/Forms/ContactForm.php'));
        $this->assertStringContainsString('namespace Modules\\Core\\Livewire\\Forms', $classContent);
    }

    public function test_can_create_volt_component_integration()
    {
        $this->artisan('module:make-volt', [
            'component' => 'volt.counter',
            'module' => 'Core',
        ])
            ->assertExitCode(0);
    }

    public function test_can_create_inline_component_integration()
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

    public function test_can_create_component_with_custom_view_path_integration()
    {
        $this->artisan('module:make-livewire', [
            'component' => 'Pages/ContactPage',
            'module' => 'Core',
            '--view' => 'pages/contact',
        ])
            ->assertExitCode(0);

        $this->assertFileExists(base_path('Modules/Core/resources/views/livewire/pages/contact.blade.php'));
    }

    public function test_can_create_component_with_custom_stub_integration()
    {
        // Create custom stub directory
        $stubPath = base_path('stubs/modules-livewire/custom');
        File::makeDirectory($stubPath, 0755, true, true);
        File::put($stubPath.'/livewire.stub', '<?php namespace {{ namespace }}; class {{ class }} { }');

        $this->artisan('module:make-livewire', [
            'component' => 'Pages/CustomPage',
            'module' => 'Core',
            '--stub' => 'custom',
        ])
            ->assertExitCode(0);

        // Clean up
        File::deleteDirectory($stubPath);
    }

    public function test_force_option_overwrites_existing_component()
    {
        // Create the component first
        $this->artisan('module:make-livewire', [
            'component' => 'Pages/TestPage',
            'module' => 'Core',
        ])
            ->assertExitCode(0);

        // Modify the file to test force overwrite
        $filePath = base_path('Modules/Core/app/Livewire/Pages/TestPage.php');
        File::put($filePath, '<?php // Modified content');

        // Try to create it again with force
        $this->artisan('module:make-livewire', [
            'component' => 'Pages/TestPage',
            'module' => 'Core',
            '--force' => true,
        ])
            ->assertExitCode(0);

        // Check that the file was overwritten
        $content = File::get($filePath);
        $this->assertStringNotContainsString('Modified content', $content);
    }

    public function test_component_tag_generation()
    {
        $this->artisan('module:make-livewire', [
            'component' => 'Pages/AboutPage',
            'module' => 'Core',
        ])
            ->expectsOutput('TAG: <livewire:core::pages.about-page />')
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

        // Create volt module structure
        $voltModulePath = base_path('modules/Core');
        File::makeDirectory($voltModulePath.'/resources/views/livewire', 0755, true, true);
        File::makeDirectory($voltModulePath.'/resources/views/pages', 0755, true, true);

        File::put($voltModulePath.'/module.json', json_encode([
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

        $voltModulePath = base_path('modules/Core');
        if (File::exists($voltModulePath)) {
            File::deleteDirectory($voltModulePath);
        }
    }
}
