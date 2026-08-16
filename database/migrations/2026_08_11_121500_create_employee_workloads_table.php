<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_workloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('allocated_percentage', 5, 2)->default(0);
            $table->decimal('allocated_hours', 8, 2)->nullable();
            $table->string('source')->default('system');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE employee_workloads ADD CONSTRAINT employee_workloads_date_check CHECK (period_end >= period_start)');
        DB::statement('ALTER TABLE employee_workloads ADD CONSTRAINT employee_workloads_percentage_check CHECK (allocated_percentage BETWEEN 0 AND 100)');
        DB::statement('ALTER TABLE employee_workloads ADD CONSTRAINT employee_workloads_hours_check CHECK (allocated_hours IS NULL OR allocated_hours >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_workloads');
    }
};
