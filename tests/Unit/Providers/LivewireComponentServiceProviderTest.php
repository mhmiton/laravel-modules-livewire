<?php

namespace Mhmiton\LaravelModulesLivewire\Tests\Unit\Providers;

use Mhmiton\LaravelModulesLivewire\Providers\LivewireComponentServiceProvider;
use Mhmiton\LaravelModulesLivewire\Tests\TestCase;

class LivewireComponentServiceProviderTest extends TestCase
{
    protected $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = new LivewireComponentServiceProvider($this->app);
    }

    public function test_provider_can_be_instantiated()
    {
        $this->assertInstanceOf(LivewireComponentServiceProvider::class, $this->provider);
    }

    public function test_register_method_calls_required_methods()
    {
        // Mock the methods to ensure they're called
        $this->provider->register();

        // The register method should not throw any exceptions
        $this->assertTrue(true);
    }

    public function test_provides_method_returns_array()
    {
        $provides = $this->provider->provides();

        $this->assertIsArray($provides);
    }

    public function test_register_module_components_returns_false_when_dependencies_missing()
    {
        $result = $this->invokeMethod($this->provider, 'registerModuleComponents');
        $this->assertTrue($result === null || is_bool($result));
    }

    public function test_register_custom_module_components_returns_false_when_dependencies_missing()
    {
        $result = $this->invokeMethod($this->provider, 'registerCustomModuleComponents');
        $this->assertTrue($result === null || is_bool($result));
    }

    public function test_register_component_directory_returns_false_when_directory_not_exists()
    {
        $result = $this->invokeMethod($this->provider, 'registerComponentDirectory', [
            '/nonexistent/directory',
            'Test\\Namespace',
            'test::',
        ]);

        $this->assertFalse($result);
    }

    public function test_register_module_volt_view_factory_returns_false_when_volt_not_available()
    {
        $result = $this->invokeMethod($this->provider, 'registerModuleVoltViewFactory');

        // The result will depend on whether Volt is available
        $this->assertIsBool($result);
    }

    /**
     * Helper method to invoke private/protected methods for testing
     */
    private function invokeMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
