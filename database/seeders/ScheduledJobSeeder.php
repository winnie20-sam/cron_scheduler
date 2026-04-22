<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScheduledJobSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('scheduled_jobs')->insert([
            [
                'name'        => 'ProcessPayments',
                'schedule'    => '* * * * *',
                'description' => 'Processes all pending payment transactions',
                'status'      => 'success',
                'last_run_at' => Carbon::now()->subMinutes(1),
                'duration_ms' => 120,
                'last_error'  => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'ReconcileAccounts',
                'schedule'    => '0 * * * *',
                'description' => 'Reconciles all account balances hourly',
                'status'      => 'success',
                'last_run_at' => Carbon::now()->subHour(),
                'duration_ms' => 340,
                'last_error'  => null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'name'        => 'GenerateStatements',
                'schedule'    => '0 0 * * *',
                'description' => 'Generates daily account statements',
                'status'      => 'failed',
                'last_run_at' => Carbon::now()->subDay(),
                'duration_ms' => 0,
                'last_error'  => 'Connection timeout while writing to storage',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }
}
