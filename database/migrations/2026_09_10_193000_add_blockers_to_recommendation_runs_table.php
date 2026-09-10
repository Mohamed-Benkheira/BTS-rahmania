<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recommendation_runs', function (Blueprint $table) {
            $table->json('blockers')->nullable()->after('criteria_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('recommendation_runs', function (Blueprint $table) {
            $table->dropColumn('blockers');
        });
    }
};
