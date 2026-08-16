<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE UNIQUE INDEX assignments_single_current ON assignments (project_id) WHERE status IN (\'approved\', \'active\')');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS assignments_single_current');
    }
};
