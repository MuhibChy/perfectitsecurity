<?php

namespace Tests\Feature;

use Tests\TestCase;

class ViewAuditTest extends TestCase
{
    public function test_all_controller_views_exist()
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(app_path('Http/Controllers')));
        $missing = [];

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                if (preg_match_all('/view\s*\(\s*[\'"]([^\'",]+)[\'"]/', $content, $matches)) {
                    foreach ($matches[1] as $viewName) {
                        $viewPath = resource_path('views/' . str_replace('.', '/', $viewName) . '.blade.php');
                        if (!file_exists($viewPath)) {
                            $missing[] = $file->getFilename() . " references missing view: {$viewName} ({$viewPath})";
                        }
                    }
                }
            }
        }

        $this->assertEmpty($missing, "Missing views detected:\n" . implode("\n", $missing));
    }
}
