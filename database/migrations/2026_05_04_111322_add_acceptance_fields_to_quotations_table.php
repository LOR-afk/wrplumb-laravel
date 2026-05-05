<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('acceptance_token')->nullable()->unique()->after('status');
            $table->string('client_response')->nullable()->after('acceptance_token');
            $table->timestamp('accepted_at')->nullable()->after('client_response');
            $table->timestamp('declined_at')->nullable()->after('accepted_at');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn([
                'acceptance_token',
                'client_response',
                'accepted_at',
                'declined_at',
            ]);
        });
    }
};