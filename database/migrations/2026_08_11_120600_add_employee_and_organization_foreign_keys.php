<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete();
        });

        Schema::table('business_units', function (Blueprint $table) {
            $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete();
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete();
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->foreignId('team_leader_id')->nullable()->constrained('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_leader_id');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manager_id');
        });

        Schema::table('business_units', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manager_id');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manager_id');
        });
    }
};
