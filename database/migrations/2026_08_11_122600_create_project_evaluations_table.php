<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->restrictOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('evaluator_id')->constrained('users')->restrictOnDelete();
            $table->smallInteger('rating');
            $table->smallInteger('communication_rating')->nullable();
            $table->smallInteger('delivery_rating')->nullable();
            $table->smallInteger('quality_rating')->nullable();
            $table->text('comments')->nullable();
            $table->timestamp('evaluated_at');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE project_evaluations ADD CONSTRAINT project_evaluations_rating_check CHECK (rating BETWEEN 1 AND 5)');
        DB::statement('ALTER TABLE project_evaluations ADD CONSTRAINT project_evaluations_communication_check CHECK (communication_rating IS NULL OR (communication_rating BETWEEN 1 AND 5))');
        DB::statement('ALTER TABLE project_evaluations ADD CONSTRAINT project_evaluations_delivery_check CHECK (delivery_rating IS NULL OR (delivery_rating BETWEEN 1 AND 5))');
        DB::statement('ALTER TABLE project_evaluations ADD CONSTRAINT project_evaluations_quality_check CHECK (quality_rating IS NULL OR (quality_rating BETWEEN 1 AND 5))');
    }

    public function down(): void
    {
        Schema::dropIfExists('project_evaluations');
    }
};
