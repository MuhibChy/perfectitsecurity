<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Per-user foreground glove ("shield") preference. Smallest viable
        // mechanism: three columns on users (no new table). Normalized
        // viewport coordinates (0..1) so one position adapts to all screens.
        Schema::table('users', function (Blueprint $table) {
            $table->string('glove_mode', 10)->default('moving')->after('preferred_locale');
            $table->float('glove_x')->nullable()->after('glove_mode');
            $table->float('glove_y')->nullable()->after('glove_x');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['glove_mode', 'glove_x', 'glove_y']);
        });
    }
};
