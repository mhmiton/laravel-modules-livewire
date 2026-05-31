<?php

namespace Mhmiton\LaravelModulesLivewire\Tests\Feature\Livewire;

use Livewire\Livewire;
use Mhmiton\LaravelModulesLivewire\Tests\TestCase;

class LivewireComponentRenderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_module_make_livewire_command_works()
    {
        $this->artisan('module:make-livewire', [
            'component' => 'Pages/AboutPage',
            'module' => 'Core',
            '--inline' => true,
        ])->assertExitCode(0);

        $componentClass = 'Modules\Core\Livewire\Pages\AboutPage';
        $componentAlias = 'core::pages.test-page';

        $this->assertFileExists(base_path('Modules/Core/app/Livewire/Pages/AboutPage.php'));

        require_once base_path('Modules/Core/app/Livewire/Pages/AboutPage.php');

        $this->assertTrue(class_exists($componentClass), 'Livewire component class was not created');

        Livewire::component($componentAlias, $componentClass);

        Livewire::test($componentAlias)
            ->assertStatus(200);

        // // Verify the files were created
        // $this->assertFileExists(base_path('Modules/Core/app/Livewire/Pages/TestPage.php'));
        // $this->assertFileExists(base_path('Modules/Core/resources/views/livewire/pages/test-page.blade.php'));

        // // Verify the component class content
        // $componentContent = file_get_contents(base_path('Modules/Core/app/Livewire/Pages/TestPage.php'));
        // $this->assertStringContainsString('class TestPage extends Component', $componentContent);
        // $this->assertStringContainsString('namespace Modules\Core\Livewire\Pages', $componentContent);

        // // Verify the view content
        // $viewContent = file_get_contents(base_path('Modules/Core/resources/views/livewire/pages/test-page.blade.php'));
        // $this->assertStringContainsString('TestPage', $viewContent);
    }
}
