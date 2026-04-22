<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthCheckController extends Controller
{
    /**
     * GET /api/health
     *
     *  Confirms the app process is running.
     *
     */
    public function live(): JsonResponse
    {
        return response()->json([
            'status'    => 'ok',
            'service'   => config('app.name', 'laravel'),
            'env'       => config('app.env'),
            'timestamp' => now()->toIso8601String(),
            'php'       => PHP_VERSION,
            'laravel'   => app()->version(),
        ]);
    }

    /**
     * GET /api/health/ready
     *
     * confirms the app AND its dependencies are healthy.
     * Returns 200 when everything is ready to serve traffic.
     * Returns 503 when any dependency is down
     */
    public function ready(): JsonResponse
    {
        $checks  = [];
        $allOk   = true;

        // 1. Database
        try {
            DB::connection()->getPdo();
            $checks['database'] = ['status' => 'ok', 'driver' => config('database.default')];
        } catch (\Throwable $e) {
            $allOk = false;
            $checks['database'] = ['status' => 'error', 'message' => $e->getMessage()];
        }

        // 2. Cache / Redis
        try {
            $key = '_healthcheck_' . time();
            Cache::put($key, 'ok', 5);
            $val = Cache::get($key);
            Cache::forget($key);

            if ($val === 'ok') {
                $checks['cache'] = ['status' => 'ok', 'driver' => config('cache.default')];
            } else {
                throw new \RuntimeException('Cache read/write mismatch');
            }
        } catch (\Throwable $e) {
            $allOk = false;
            $checks['cache'] = ['status' => 'error', 'message' => $e->getMessage()];
        }

        // 3. Storage (disk writable?)
        try {
            $path = storage_path('app/.hc_probe');
            file_put_contents($path, '1');
            unlink($path);
            $checks['storage'] = ['status' => 'ok'];
        } catch (\Throwable $e) {
            $allOk = false;
            $checks['storage'] = ['status' => 'error', 'message' => $e->getMessage()];
        }

        return response()->json([
            'status'    => $allOk ? 'ready' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'checks'    => $checks,
        ], $allOk ? 200 : 503);
    }
}
