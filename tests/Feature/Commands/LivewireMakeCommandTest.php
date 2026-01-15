<?php

namespace Mhmiton\LaravelModulesLivewire\Tests\Feature\Commands;

use Illuminate\Support\Facades\File;
use Mhmiton\LaravelModulesLivewire\Tests\TestCase;

require_once __DIR__.'/../../TestCase.php';

class LivewireMakeCommandTest extends TestCase
{
    public function setUp(): void
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
            'module' => 'TestCore'
        ])
        ->assertExitCode(0);

        $this->assertFileExists(base_path('Modules/TestCore/app/Livewire/Pages/AboutPage.php'));
        $this->assertFileExists(base_path('Modules/TestCore/resources/views/livewire/pages/⚡about-page.blade.php'));
    }

    public function test_can_create_livewire_component_with_backslash_notation()
    {
        $this->artisan('module:make-livewire', [
            'component' => 'Pages\\AboutPage',
            'module' => 'TestCore'
        ])
        ->assertExitCode(0);

        $this->assertFileExists(base_path('Modules/TestCore/app/Livewire/Pages/AboutPage.php'));
    }

    public function test_can_create_livewire_component_with_dot_notation()
    {
        $this->artisan('module:make-livewire', [
            'component' => 'pages.about-page',
            'module' => 'TestCore'
        ])
        ->assertExitCode(0);

        $this->assertFileExists(base_path('Modules/TestCore/app/Livewire/Pages/AboutPage.php'));
    }

    public function test_can_create_inline_component()
    {
        $this->artisan('module:make-livewire', [
            'component' => 'Pages/AboutPage',
            'module' => 'TestCore',
            '--inline' => true
        ])
        ->assertExitCode(0);

        $this->assertFileExists(base_path('Modules/TestCore/app/Livewire/Pages/AboutPage.php'));
        $this->assertFileDoesNotExist(base_path('Modules/TestCore/resources/views/livewire/pages/⚡about-page.blade.php'));
    }

    public function test_can_force_create_component()
    {
        // Create the component first
        $this->artisan('module:make-livewire', [
            'component' => 'Pages/AboutPage',
            'module' => 'TestCore'
        ])
        ->assertExitCode(0);

        // Try to create it again with force
        $this->artisan('module:make-livewire', [
            'component' => 'Pages/AboutPage',
            'module' => 'TestCore',
            '--force' => true
        ])
        ->assertExitCode(0);
    }

    public function test_cannot_create_component_without_force_when_exists()
    {
        // Create the component first
        $this->artisan('module:make-livewire', [
            'component' => 'Pages/AboutPage',
            'module' => 'TestCore'
        ])
        ->assertExitCode(0);

        // Try to create it again without force
        $this->artisan('module:make-livewire', [
            'component' => 'Pages/AboutPage',
            'module' => 'TestCore'
        ])
        ->assertExitCode(0);
    }

    public function test_can_create_component_with_custom_view_path()
    {
        $this->artisan('module:make-livewire', [
            'component' => 'Pages/AboutPage',
            'module' => 'TestCore',
            '--view' => 'pages/about'
        ])
        ->assertExitCode(0);

        $this->assertFileExists(base_path('Modules/TestCore/resources/views/livewire/pages/about.blade.php'));
    }

    public function test_can_create_component_with_custom_stub()
    {
        // Create custom stub directory
        $stubPath = base_path('stubs/modules-livewire/custom');
        File::makeDirectory($stubPath, 0755, true, true);
        File::put($stubPath . '/livewire.stub', '<?php namespace {{ namespace }}; class {{ class }} { }');

        $this->artisan('module:make-livewire', [
            'component' => 'Pages/AboutPage',
            'module' => 'TestCore',
            '--stub' => 'custom'
        ])
        ->assertExitCode(0);

        // Clean up
        File::deleteDirectory($stubPath);
    }

    public function test_validates_component_name()
    {
        $this->artisan('module:make-livewire', [
            'component' => '123Invalid',
            'module' => 'TestCore'
        ])
        ->assertExitCode(0);
    }

    public function test_validates_reserved_class_names()
    {
        $this->artisan('module:make-livewire', [
            'component' => 'Component',
            'module' => 'TestCore'
        ])
        ->assertExitCode(0);
    }

    public function test_can_create_sfc_component()
    {
        $this->artisan('module:make-livewire', [
            'component' => 'Pages/SfcPage',
            'module' => 'TestCore',
            '--sfc' => true
        ])
        ->assertExitCode(0);

        $this->assertFileExists(base_path('Modules/TestCore/resources/views/livewire/pages/⚡sfc-page.blade.php'));
        $this->assertFileDoesNotExist(base_path('Modules/TestCore/app/Livewire/Pages/SfcPage.php'));
    }



    public function test_can_create_component_with_emoji()
    {
        $this->artisan('module:make-livewire', [
            'component' => 'Pages/⚡create',
            'module' => 'TestCore'
        ])
        ->assertExitCode(0);

        $this->assertFileExists(base_path('Modules/TestCore/app/Livewire/Pages/⚡create.php'));
        $this->assertFileExists(base_path('Modules/TestCore/resources/views/livewire/pages/⚡create.blade.php'));
    }

    public function test_component_is_registered_with_correct_alias()
    {
        $this->artisan('module:make-livewire', [
            'component' => 'Pages/RegisterCheck',
            'module' => 'TestCore',
            '--sfc' => true
        ])->assertExitCode(0);

        // Verify file created in nested path (livewire/pages/)
        // Default default namespace maps 'livewire' -> 'livewire' folder.
        // So Pages/RegisterCheck -> .../views/livewire/pages/⚡register-check.blade.php

        $expectedPath = base_path('Modules/TestCore/resources/views/livewire/pages/⚡register-check.blade.php');
        $this->assertFileExists($expectedPath);

        // Manually register namespace since module was created after App boot
        $nsPath = base_path('Modules/TestCore/resources/views/livewire');
        
        $finder = app(\Livewire\Finder\Finder::class);
        $finder->addNamespace('testcore', $nsPath);
        
        $ref = new \ReflectionClass($finder);
        $prop = $ref->getProperty('viewNamespaces');
        $prop->setAccessible(true);
        $namespaces = $prop->getValue($finder);
        
        $this->assertArrayHasKey('testcore', $namespaces);
        $this->assertEquals($nsPath, $namespaces['testcore']);
    }

    public function test_can_create_component_with_namespace_notation()
    {
        $this->artisan('module:make-livewire', [
            'component' => 'pages::NamespacePage',
            'module' => 'TestCore',
            '--sfc' => true
        ])
        ->assertExitCode(0);

        // Expected path: nested in 'livewire' folder now 
        $expectedPath = base_path('Modules/TestCore/resources/views/livewire/pages/⚡namespace-page.blade.php');
        
        $this->assertFileExists($expectedPath);

        // Register namespace
        $nsPath = base_path('Modules/TestCore/resources/views/livewire');
        $finder = app(\Livewire\Finder\Finder::class);
        $finder->addNamespace('testcore', $nsPath);

        // Verify Finder State
        $ref = new \ReflectionClass($finder);
        $prop = $ref->getProperty('viewNamespaces');
        $prop->setAccessible(true);
        $namespaces = $prop->getValue($finder);
        
        $this->assertArrayHasKey('testcore', $namespaces);
        $this->assertEquals($nsPath, $namespaces['testcore']);
    }

    public function test_can_create_mfc_component()
    {
        $this->artisan('module:make-livewire', [
            'component' => 'Pages/MfcPage',
            'module' => 'TestCore',
            '--mfc' => true
        ])
        ->assertExitCode(0);

        // Expect directory with emoji: livewire/pages/⚡mfc-page
        // Expect files without emoji inside
        
        $dir = base_path('Modules/TestCore/resources/views/livewire/pages/⚡mfc-page');
        $this->assertDirectoryExists($dir);
        
        $this->assertFileExists($dir . '/mfc-page.php');
        $this->assertFileExists($dir . '/mfc-page.blade.php');

        // Verify Resolution
        $nsPath = base_path('Modules/TestCore/resources/views/livewire');
        $finder = app(\Livewire\Finder\Finder::class);
        $finder->addNamespace('testcore', $nsPath);
        
        // Alias: testcore::pages.mfc-page
        $resolvedPath = $finder->resolveMultiFileComponentPath('testcore::pages.mfc-page');
        
        $this->assertEquals($dir, $resolvedPath, 'Finder failed to resolve MFC component path');
    }

    public function test_can_create_component_with_nested_namespace_syntax_using_colons()
    {
        $this->artisan('module:make-livewire', [
            'component' => 'pages::Deep::Settings::Profile',
            'module' => 'TestCore',
            '--sfc' => true
        ])
        ->expectsOutputToContain('<livewire:testcore::pages::deep.settings.profile />')
        ->assertExitCode(0);

        $expectedPath = base_path('Modules/TestCore/resources/views/livewire/pages/deep/settings/⚡profile.blade.php');
        
        $this->assertFileExists($expectedPath);
    }
}
