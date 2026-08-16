<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->restrictOnDelete();
            $table->smallInteger('proficiency_level')->nullable();
            $table->decimal('years_experience', 5, 2)->nullable();
            $table->date('last_used_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'skill_id']);
        });

        DB::statement('ALTER TABLE employee_skills ADD CONSTRAINT employee_skills_proficiency_check CHECK (proficiency_level IS NULL OR (proficiency_level BETWEEN 1 AND 5))');
        DB::statement('ALTER TABLE employee_skills ADD CONSTRAINT employee_skills_experience_check CHECK (years_experience IS NULL OR years_experience >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_skills');
    }
};
