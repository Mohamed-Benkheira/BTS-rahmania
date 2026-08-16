<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('availability_percentage', 5, 2)->default(100);
            $table->enum('status', ['available', 'partially_available', 'unavailable', 'on_leave'])->default('available');
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE employee_availabilities ADD CONSTRAINT employee_availabilities_date_check CHECK (end_date >= start_date)');
        DB::statement('ALTER TABLE employee_availabilities ADD CONSTRAINT employee_availabilities_percentage_check CHECK (availability_percentage BETWEEN 0 AND 100)');
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_availabilities');
    }
};
