<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "Starting Phase 5 Database Migration...\n";

Schema::table('tickets', function (Blueprint $table) {
    if (!Schema::hasColumn('tickets', 'contact_person')) {
        $table->string('contact_person')->nullable()->after('description');
        echo "Column 'contact_person' added.\n";
    }
    if (!Schema::hasColumn('tickets', 'contact_number')) {
        $table->string('contact_number')->nullable()->after('contact_person');
        echo "Column 'contact_number' added.\n";
    }
});

echo "Phase 5 Database Setup Complete.\n";
