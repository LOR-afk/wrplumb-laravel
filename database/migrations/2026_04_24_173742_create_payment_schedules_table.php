<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();

            $table->string('label'); // Downpayment, Final, Progress 1, etc.
            $table->decimal('percent', 5, 2)->default(0);
            $table->decimal('amount_due', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);

            $table->date('due_date')->nullable();
            $table->string('status')->default('pending'); // pending, partial, paid, overdue
            $table->text('notes')->nullable();

            $table->unsignedInteger('sort_order')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_schedules');
    }
};