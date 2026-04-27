<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspector_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspector_id')->constrained('users')->cascadeOnDelete();
            $table->date('availability_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('status', ['available', 'on_duty', 'off_duty', 'on_leave'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspector_availabilities');
    }
};