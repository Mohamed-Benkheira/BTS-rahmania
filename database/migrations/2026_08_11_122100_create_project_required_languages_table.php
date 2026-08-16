<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_required_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->restrictOnDelete();
            $table->enum('minimum_level', ['beginner', 'intermediate', 'advanced', 'native'])->nullable();
            $table->boolean('is_mandatory')->default(false);
            $table->timestamps();

            $table->unique(['project_id', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_required_languages');
    }
};
