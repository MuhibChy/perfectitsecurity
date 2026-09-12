<?php
// app/Services/HealthCheckService.php

namespace App\Services;

use App\Models\HealthCheckLog;
use App\Models\FailbackSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HealthCheckService
{
    protected $settings = [];
    protected $failedChecks = [];
    protected $allChecksPassed = true;

    public function __construct()
    {
        $this->loadSettings();
    }

    protected function loadSettings()
    {
        $this->settings = FailbackSetting::pluck('setting_value', 'setting_key')->toArray();
    }

    public function runAllChecks()
    {
        $results = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            'api' => $this->checkApi(),
            'frontend' => $this->checkFrontend(),
            'services' => $this->checkServices(),
            'routes' => $this->checkRoutes(),
            'storage' => $this->checkStorage(),
        ];

        $allPassed = !in_array(false, $results);
        
        if (!$allPassed) {
            $this->handleFailure($results);
        }

        return [
            'healthy' => $allPassed,
            'results' => $results,
            'timestamp' => now(),
        ];
    }

    protected function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            $result = DB::select('SELECT 1 as connection_test');
            return !empty($result);
        } catch (\Exception $e) {
            $this->logCheckFailure('database', $e->getMessage());
            return false;
        }
    }

    protected function checkCache()
    {
        try {
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'test', 10);
            $value = Cache::get($testKey);
            Cache::forget($testKey);
            return $value === 'test';
        } catch (\Exception $e) {
            $this->logCheckFailure('cache', $e->getMessage());
            return false;
        }
    }

    protected function checkQueue()
    {
        try {
            $queueConnection = config('queue.default');
            return true; // Simplified check
        } catch (\Exception $e) {
            $this->logCheckFailure('queue', $e->getMessage());
            return false;
        }
    }

    protected function checkApi()
    {
        try {
            $response = Http::timeout(5)->get(url('/api/health'));
            return $response->successful();
        } catch (\Exception $e) {
            $this->logCheckFailure('api', $e->getMessage());
            return false;
        }
    }

    protected function checkFrontend()
    {
        try {
            $response = Http::timeout(5)->get(url('/'));
            return $response->successful() && $response->status() === 200;
        } catch (\Exception $e) {
            $this->logCheckFailure('frontend', $e->getMessage());
            return false;
        }
    }

    protected function checkServices()
    {
        $criticalServices = [
            'ticket_creation',
            'invoice_generation',
            'payment_processing',
            'notification_sending'
        ];

        foreach ($criticalServices as $service) {
            try {
                // Implement service-specific checks
                // This is a placeholder - implement actual service checks
                $status = $this->checkSpecificService($service);
                if (!$status) {
                    $this->logCheckFailure($service, 'Service check failed');
                    return false;
                }
            } catch (\Exception $e) {
                $this->logCheckFailure($service, $e->getMessage());
                return false;
            }
        }
        return true;
    }

    protected function checkSpecificService($service)
    {
        // Implement specific service health checks
        switch ($service) {
            case 'ticket_creation':
                // Test ticket creation endpoint
                return true;
            case 'invoice_generation':
                // Test invoice generation
                return true;
            case 'payment_processing':
                // Test payment gateway
                return true;
            case 'notification_sending':
                // Test notification system
                return true;
            default:
                return true;
        }
    }

    protected function checkRoutes()
    {
        $criticalRoutes = [
            '/',
            '/login',
            '/register',
            '/api/tickets',
            '/api/services',
            '/api/clients',
            '/dashboard',
        ];

        foreach ($criticalRoutes as $route) {
            try {
                $response = Http::timeout(3)->get(url($route));
                if (!$response->successful()) {
                    $this->logCheckFailure('route_' . $route, 'Route returned ' . $response->status());
                    return false;
                }
            } catch (\Exception $e) {
                $this->logCheckFailure('route_' . $route, $e->getMessage());
                return false;
            }
        }
        return true;
    }

    protected function checkStorage()
    {
        try {
            $storagePath = storage_path();
            return is_writable($storagePath);
        } catch (\Exception $e) {
            $this->logCheckFailure('storage', $e->getMessage());
            return false;
        }
    }

    protected function logCheckFailure($checkName, $errorMessage)
    {
        HealthCheckLog::create([
            'check_name' => $checkName,
            'check_type' => 'service',
            'is_healthy' => false,
            'error_message' => $errorMessage,
        ]);
        $this->failedChecks[] = $checkName;
        $this->allChecksPassed = false;
    }

    protected function handleFailure($results)
    {
        $this->triggerRollback($results);
        $this->sendAlert($results);
    }

    protected function triggerRollback($results)
    {
        $autoRollback = $this->settings['auto_rollback_enabled'] ?? 'true';
        
        if ($autoRollback === 'true') {
            $rollbackService = app(RollbackService::class);
            $rollbackService->triggerAutomaticRollback($results);
        }
    }

    protected function sendAlert($results)
    {
        $email = $this->settings['emergency_contact_email'] ?? 'admin@example.com';
        $this->sendNotification($email, $results);
    }

    protected function sendNotification($email, $results)
    {
        // Send email notification about health check failure
        // Use Laravel Mail or Notification
        \Log::error('Health Check Failed', [
            'results' => $results,
            'timestamp' => now()
        ]);
    }
}

