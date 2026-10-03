<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class ProjectVerify extends Command
{
    protected $signature = 'project:verify {--baseline : Regenerate baseline from current state}';

    protected $description = 'Verify PerfectITSecurity project baseline integrity';

    public function handle()
    {
        $this->info('PerfectITSecurity Project Verification');
        $this->newLine();

        $results = [
            'Architecture' => $this->checkArchitecture(),
            'Routes' => $this->checkRoutes(),
            'Database' => $this->checkDatabase(),
            'Authentication' => $this->checkAuthentication(),
            'Core Features' => $this->checkCoreFeatures(),
            'Theme System' => $this->checkThemeSystem(),
            'Moving Glove' => $this->checkGlove(),
            'Dependencies' => $this->checkDependencies(),
        ];

        $this->newLine();
        $this->table(
            ['Component', 'Status'],
            collect($results)->map(fn ($status, $component) => [$component, $status ? 'PASS' : 'FAIL'])->toArray()
        );

        $allPassed = collect($results)->every(fn ($status) => $status);

        $this->newLine();
        if ($allPassed) {
            $this->info('BASELINE STATUS: HEALTHY');
        } else {
            $this->error('BASELINE STATUS: ISSUES DETECTED');
        }

        if ($this->option('baseline')) {
            $this->regenerateBaseline();
        }

        return $allPassed ? Command::SUCCESS : Command::FAILURE;
    }

    private function checkArchitecture(): bool
    {
        try {
            // Check key directories exist
            $requiredDirs = [
                app_path('Http/Controllers'),
                app_path('Models'),
                resource_path('views'),
                resource_path('views/layouts'),
                resource_path('views/components'),
            ];

            foreach ($requiredDirs as $dir) {
                if (! File::exists($dir)) {
                    $this->error("Missing directory: {$dir}");

                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            $this->error("Architecture check failed: {$e->getMessage()}");

            return false;
        }
    }

    private function checkRoutes(): bool
    {
        try {
            $routes = Route::getRoutes();
            if (count($routes) === 0) {
                $this->error('No routes registered');

                return false;
            }

            // Check for critical route groups
            $criticalRoutes = ['home', 'login', 'register', 'admin.dashboard', 'portal.dashboard'];
            foreach ($criticalRoutes as $routeName) {
                if (! Route::has($routeName)) {
                    $this->warn("Missing critical route: {$routeName}");
                }
            }

            return true;
        } catch (\Exception $e) {
            $this->error("Routes check failed: {$e->getMessage()}");

            return false;
        }
    }

    private function checkDatabase(): bool
    {
        try {
            if (! Schema::hasTable('users')) {
                $this->error('Users table missing');

                return false;
            }

            return true;
        } catch (\Exception $e) {
            $this->error("Database check failed: {$e->getMessage()}");

            return false;
        }
    }

    private function checkAuthentication(): bool
    {
        try {
            // Check auth layout exists
            if (! File::exists(resource_path('views/layouts/app.blade.php'))) {
                $this->error('Authenticated layout missing');

                return false;
            }

            return true;
        } catch (\Exception $e) {
            $this->error("Authentication check failed: {$e->getMessage()}");

            return false;
        }
    }

    private function checkCoreFeatures(): bool
    {
        try {
            // Check key controllers exist
            $requiredControllers = [
                'AuthController',
                'Admin/UserController',
                'ServiceController',
            ];

            foreach ($requiredControllers as $controller) {
                $controllerPath = app_path("Http/Controllers/{$controller}.php");
                if (! File::exists($controllerPath)) {
                    $this->line("Controller may be missing: {$controller}", 'comment');
                }
            }

            return true;
        } catch (\Exception $e) {
            $this->error("Core features check failed: {$e->getMessage()}");

            return false;
        }
    }

    private function checkThemeSystem(): bool
    {
        try {
            // Check theme files exist
            if (! File::exists(resource_path('css/app.css'))) {
                $this->error('CSS file missing');

                return false;
            }

            if (! File::exists(resource_path('js/app.js'))) {
                $this->error('JS file missing');

                return false;
            }

            return true;
        } catch (\Exception $e) {
            $this->error("Theme system check failed: {$e->getMessage()}");

            return false;
        }
    }

    private function checkGlove(): bool
    {
        try {
            // Check glove component exists
            if (! File::exists(resource_path('views/components/glove-control.blade.php'))) {
                $this->error('Glove control component missing');

                return false;
            }

            // Check 3D scene component exists
            if (! File::exists(resource_path('views/components/global-3d-scene.blade.php'))) {
                $this->error('3D scene component missing');

                return false;
            }

            // Check 3D JS exists
            if (! File::exists(resource_path('js/global-3d.js'))) {
                $this->error('3D JavaScript file missing');

                return false;
            }

            return true;
        } catch (\Exception $e) {
            $this->error("Glove check failed: {$e->getMessage()}");

            return false;
        }
    }

    private function checkDependencies(): bool
    {
        try {
            // Check composer.json exists
            if (! File::exists(base_path('composer.json'))) {
                $this->error('composer.json missing');

                return false;
            }

            // Check package.json exists
            if (! File::exists(base_path('package.json'))) {
                $this->error('package.json missing');

                return false;
            }

            return true;
        } catch (\Exception $e) {
            $this->error("Dependencies check failed: {$e->getMessage()}");

            return false;
        }
    }

    private function regenerateBaseline(): void
    {
        $this->info('Regenerating baseline...');

        $baseline = [
            'project' => 'PerfectITSecurity',
            'baseline_version' => '1.0.0',
            'generated_at' => now()->toIso8601String(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
        ];

        $baselinePath = base_path('docs/project-baseline/PROJECT_BASELINE.json');
        File::put($baselinePath, json_encode($baseline, JSON_PRETTY_PRINT));

        $this->info("Baseline regenerated: {$baselinePath}");
    }
}
