<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->string('responsibility')->nullable();
            $table->decimal('allocation_percentage', 5, 2)->nullable();
            $table->decimal('allocated_hours', 10, 2)->nullable();
            $table->date('joined_at')->nullable();
            $table->date('left_at')->nullable();
            $table->timestamps();

            $table->unique(['assignment_id', 'employee_id']);
        });

        DB::statement('ALTER TABLE assignment_members ADD CONSTRAINT assignment_members_allocation_check CHECK (allocation_percentage IS NULL OR (allocation_percentage BETWEEN 0 AND 100))');
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_members');
    }
};
