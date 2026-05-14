<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('address');
            $table->string('email')->nullable()->after('phone');
        });

        Schema::table('sla_levels', function (Blueprint $table) {
            $table->integer('reminder_interval_minutes')->default(60)->after('resolution_time_minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn(['phone', 'email']);
        });

        Schema::table('sla_levels', function (Blueprint $table) {
            $table->dropColumn('reminder_interval_minutes');
        });
    }
};
