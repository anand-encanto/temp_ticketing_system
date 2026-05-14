<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "Adding User/Outlet detail fields (Req 14)...\n";

Schema::table('users', function (Blueprint $table) {
    if (!Schema::hasColumn('users', 'outlet_phone')) {
        $table->string('outlet_phone')->nullable();
    }
    if (!Schema::hasColumn('users', 'outlet_email')) {
        $table->string('outlet_email')->nullable();
    }
    if (!Schema::hasColumn('users', 'mobile')) {
        $table->string('mobile')->nullable();
    }
});

echo "User fields updated.\n";
