<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Blade;

class BladeCompileTest extends TestCase
{
    public function test_all_blade_views_compile()
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views')));
        $failed = [];

        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $viewContent = file_get_contents($file->getPathname());
                try {
                    Blade::compileString($viewContent);
                } catch (\Throwable $e) {
                    $failed[] = $file->getPathname() . ': ' . $e->getMessage();
                }
            }
        }

        $this->assertEmpty($failed, "Blade syntax errors found:\n" . implode("\n", $failed));
    }
}
