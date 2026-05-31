<?php

namespace Mhmiton\LaravelModulesLivewire\Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test that basic PHP functionality works.
     */
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }

    /**
     * Test that basic arithmetic works.
     */
    public function test_basic_arithmetic(): void
    {
        $this->assertEquals(4, 2 + 2);
        $this->assertEquals(0, 2 - 2);
        $this->assertEquals(4, 2 * 2);
        $this->assertEquals(1, 2 / 2);
    }

    /**
     * Test that string operations work.
     */
    public function test_string_operations(): void
    {
        $this->assertEquals('Hello World', 'Hello '.'World');
        $this->assertEquals(5, strlen('Hello'));
        $this->assertEquals('HELLO', strtoupper('hello'));
    }

    /**
     * Test that array operations work.
     */
    public function test_array_operations(): void
    {
        $array = [1, 2, 3];
        $this->assertCount(3, $array);
        $this->assertEquals(1, $array[0]);
        $this->assertEquals(3, count($array));
    }
}
