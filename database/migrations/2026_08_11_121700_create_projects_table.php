<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained('project_categories')->restrictOnDelete();
            $table->foreignId('requesting_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('owning_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('project_manager_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['draft', 'submitted', 'under_review', 'staffing', 'assigned', 'in_progress', 'completed', 'cancelled', 'archived'])->default('draft');
            $table->enum('confidentiality_level', ['public', 'internal', 'confidential', 'restricted'])->default('internal');
            $table->date('start_date')->nullable();
            $table->date('target_end_date')->nullable();
            $table->decimal('estimated_hours', 10, 2)->nullable();
            $table->decimal('estimated_budget', 14, 2)->nullable();
            $table->integer('required_members_count')->default(1);
            $table->enum('assignment_mode', ['single_employee', 'multiple_employees', 'team', 'department'])->default('multiple_employees');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('ALTER TABLE projects ADD CONSTRAINT projects_dates_check CHECK (target_end_date IS NULL OR start_date IS NULL OR target_end_date >= start_date)');
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
