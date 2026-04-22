<?php

namespace App\Http\Controllers;

use App\Models\ScheduledJob;
use Illuminate\Http\JsonResponse;

class ScheduledJobController extends Controller
{
    /**
     * GET /api/jobs
     *
     * Returns all scheduled jobs and their current status.
     * Think of this as a fintech cron monitor — shows which
     * payment jobs are healthy and which have failed.
     */
    public function index(): JsonResponse
    {
        $jobs = ScheduledJob::orderBy('last_run_at', 'desc')->get();

        $total   = $jobs->count();
        $failed  = $jobs->where('status', 'failed')->count();
        $healthy = $jobs->where('status', 'success')->count();

        return response()->json([
            'summary' => [
                'total'   => $total,
                'healthy' => $healthy,
                'failed'  => $failed,
                'status'  => $failed > 0 ? 'degraded' : 'ok',
            ],
            'jobs' => $jobs->map(fn($job) => [
                'id'          => $job->id,
                'name'        => $job->name,
                'schedule'    => $job->schedule,
                'description' => $job->description,
                'status'      => $job->status,
                'last_run_at' => $job->last_run_at?->toIso8601String(),
                'duration_ms' => $job->duration_ms,
                'last_error'  => $job->last_error,
            ]),
        ]);
    }

    /**
     * POST /api/jobs/{id}/run
     *
     * Manually triggers a job and marks it as running.
     * Simulates what a DevOps engineer would do to
     * manually kick off a failed payment job.
     */
    public function run(int $id): JsonResponse
    {
        $job = ScheduledJob::findOrFail($id);

        $job->update([
            'status'      => 'success',
            'last_run_at' => now(),
            'duration_ms' => rand(80, 400),
            'last_error'  => null,
        ]);

        return response()->json([
            'message'    => "Job {$job->name} triggered successfully",
            'job'        => $job->fresh(),
            'timestamp'  => now()->toIso8601String(),
        ]);
    }
}
