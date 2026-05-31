<?php

namespace Mhmiton\LaravelModulesLivewire\Tests\Feature\Volt;

use Mhmiton\LaravelModulesLivewire\Tests\TestCase;

class VoltComponentRenderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_module_make_volt_command_can_be_called()
    {
        $this->artisan('module:make-volt', [
            'component' => 'volt.counter',
            'module' => 'Core',
        ]);

        $this->assertTrue(true);
    }
}
