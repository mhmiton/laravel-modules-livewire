<?php

namespace Mhmiton\LaravelModulesLivewire\Tests\Traits;

use Illuminate\Support\Facades\File;

trait InitModule
{
    protected function setUpModule(): void
    {
        $this->createTestModule();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->cleanupTestModule();
    }

    protected function createTestModule()
    {
        // Ensure modules directory exists for testing
        if (! is_dir(base_path('Modules'))) {
            mkdir(base_path('Modules'), 0777, true);
        }

        $this->artisan('module:make', ['name' => ['TestCore'], '--force' => true]);

        $this->assertTrue($this->hasTestModule(), 'Module was not created');
    }

    protected function cleanupTestModule()
    {
        if ($this->hasTestModule()) {
            File::deleteDirectory(base_path('Modules/TestCore'));

            // Remove from status json? 
            // Better not to touch modules_statuses.json if possible or be careful.
            // If running in app, modifying `modules_statuses.json` affects app.
            // Maybe safer to not clean up or just leave it?
            // But tests should be isolated.
        }
    }

    protected function hasTestModule()
    {
        return File::exists(base_path('Modules/TestCore/module.json'));
    }
}
