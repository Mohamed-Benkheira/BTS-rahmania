<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('employee_code')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->date('birth_date')->nullable();
            $table->date('hire_date')->nullable();
            $table->enum('employment_type', ['full_time', 'part_time', 'contractor', 'intern'])->default('full_time');
            $table->enum('employment_status', ['active', 'on_leave', 'suspended', 'resigned', 'retired'])->default('active');
            $table->foreignId('business_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('primary_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->text('biography')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
