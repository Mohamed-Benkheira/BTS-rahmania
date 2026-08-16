<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->restrictOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('assignment_type', ['employee', 'team', 'department', 'mixed']);
            $table->enum('status', ['pending', 'approved', 'active', 'completed', 'rejected', 'cancelled'])->default('pending');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE assignments ADD CONSTRAINT assignments_dates_check CHECK (end_date IS NULL OR start_date IS NULL OR end_date >= start_date)');
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
