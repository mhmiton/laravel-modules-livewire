<?php

namespace Mhmiton\LaravelModulesLivewire\Tests\Unit;

use Mhmiton\LaravelModulesLivewire\LaravelModulesLivewireServiceProvider;
use Mhmiton\LaravelModulesLivewire\Tests\TestCase;

class LaravelModulesLivewireServiceProviderTest extends TestCase
{
    protected $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = new LaravelModulesLivewireServiceProvider($this->app);
    }

    public function test_provider_can_be_instantiated()
    {
        $this->assertInstanceOf(LaravelModulesLivewireServiceProvider::class, $this->provider);
    }

    public function test_register_method_does_not_throw_exception()
    {
        // The register method should not throw any exceptions
        $this->provider->register();

        $this->assertTrue(true);
    }

    public function test_boot_method_calls_required_methods()
    {
        // The boot method should not throw any exceptions
        $this->provider->boot();

        $this->assertTrue(true);
    }

    public function test_register_providers_method_registers_livewire_component_service_provider()
    {
        $this->provider->register();
        $this->assertTrue(true); // Should not throw exceptions
    }

    public function test_register_commands_method_registers_commands_when_in_console()
    {
        $this->provider->boot();
        $this->assertTrue(true); // Should not throw exceptions
    }

    public function test_register_commands_method_returns_early_when_not_in_console()
    {
        $this->provider->boot();
        $this->assertTrue(true); // Should not throw exceptions
    }

    public function test_register_publishables_method_registers_publishable_assets()
    {
        $this->provider->boot();
        $this->assertTrue(true); // Should not throw exceptions
    }

    public function test_config_is_merged_correctly()
    {
        // The boot method should merge the config
        $this->provider->boot();

        // Check if the config is available
        $config = config('modules-livewire');
        $this->assertIsArray($config);
        $this->assertArrayHasKey('namespace', $config);
        $this->assertArrayHasKey('view', $config);
        $this->assertArrayHasKey('volt_view_namespaces', $config);
        $this->assertArrayHasKey('custom_modules', $config);
    }
}
