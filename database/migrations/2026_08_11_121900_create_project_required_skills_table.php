<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_required_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->restrictOnDelete();
            $table->smallInteger('minimum_proficiency')->nullable();
            $table->decimal('minimum_years_experience', 5, 2)->nullable();
            $table->boolean('is_mandatory')->default(false);
            $table->decimal('weight', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'skill_id']);
        });

        DB::statement('ALTER TABLE project_required_skills ADD CONSTRAINT project_required_skills_proficiency_check CHECK (minimum_proficiency IS NULL OR (minimum_proficiency BETWEEN 1 AND 5))');
    }

    public function down(): void
    {
        Schema::dropIfExists('project_required_skills');
    }
};
