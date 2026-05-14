<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "Starting Phase 2 Database Migration...\n";

// 1. Create sla_levels table
if (!Schema::hasTable('sla_levels')) {
    Schema::create('sla_levels', function (Blueprint $table) {
        $table->id();
        $table->enum('priority', ['Low', 'Medium', 'High', 'Urgent'])->unique();
        $table->integer('response_time_minutes')->default(60);
        $table->integer('resolution_time_minutes')->default(1440);
        $table->timestamps();
    });
    echo "Table 'sla_levels' created.\n";

    // Insert Default McDonald's SLA Levels
    DB::table('sla_levels')->insert([
        ['priority' => 'Urgent', 'response_time_minutes' => 30, 'resolution_time_minutes' => 120],
        ['priority' => 'High', 'response_time_minutes' => 60, 'resolution_time_minutes' => 480],
        ['priority' => 'Medium', 'response_time_minutes' => 240, 'resolution_time_minutes' => 1440],
        ['priority' => 'Low', 'response_time_minutes' => 480, 'resolution_time_minutes' => 2880],
    ]);
    echo "Default SLA levels inserted.\n";
} else {
    echo "Table 'sla_levels' already exists.\n";
}

// 2. Update tickets table
Schema::table('tickets', function (Blueprint $table) {
    if (!Schema::hasColumn('tickets', 'due_at')) {
        $table->timestamp('due_at')->nullable()->after('expected_resolution_time');
        echo "Column 'due_at' added to 'tickets'.\n";
    }
    if (!Schema::hasColumn('tickets', 'is_overdue')) {
        $table->boolean('is_overdue')->default(0)->after('due_at');
        echo "Column 'is_overdue' added to 'tickets'.\n";
    }
    if (!Schema::hasColumn('tickets', 'last_reminder_sent_at')) {
        $table->timestamp('last_reminder_sent_at')->nullable()->after('is_overdue');
        echo "Column 'last_reminder_sent_at' added to 'tickets'.\n";
    }
});

echo "Phase 2 Database Setup Complete.\n";
