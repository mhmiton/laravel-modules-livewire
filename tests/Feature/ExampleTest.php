<?php

namespace Mhmiton\LaravelModulesLivewire\Tests\Feature;

use Mhmiton\LaravelModulesLivewire\LaravelModulesLivewireServiceProvider;
use Mhmiton\LaravelModulesLivewire\Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test that the package can be loaded and configured.
     */
    public function test_package_can_be_loaded(): void
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

    /**
     * Test that the package provides the expected configuration.
     */
    public function test_package_provides_expected_configuration(): void
    {
        $config = config('modules-livewire');

        $this->assertEquals('Livewire', $config['namespace']);
        $this->assertEquals('resources/views/livewire', $config['view']);
        $this->assertEquals(['livewire', 'pages'], $config['volt_view_namespaces']);
        $this->assertIsArray($config['custom_modules']);
    }

    /**
     * Test that the package can handle basic arithmetic (original test).
     */
    public function test_sum_2_and_2(): void
    {
        $result = 2 + 2;

        $this->assertEquals(4, $result);
    }
}
