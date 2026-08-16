<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->restrictOnDelete();
            $table->enum('speaking_level', ['beginner', 'intermediate', 'advanced', 'native'])->nullable();
            $table->enum('writing_level', ['beginner', 'intermediate', 'advanced', 'native'])->nullable();
            $table->enum('reading_level', ['beginner', 'intermediate', 'advanced', 'native'])->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_languages');
    }
};
