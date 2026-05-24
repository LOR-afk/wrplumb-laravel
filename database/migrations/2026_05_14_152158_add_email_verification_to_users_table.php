<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Already handled in the original users table migration.
    }

    public function down(): void
    {
        // No rollback needed.
    }
};