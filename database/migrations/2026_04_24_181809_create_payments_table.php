<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();

            $table->foreignId('payment_schedule_id')
                ->nullable()
                ->constrained('payment_schedules')
                ->nullOnDelete();

            $table->foreignId('received_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('payment_no')->unique();
            $table->string('payment_type')->nullable(); // downpayment, final, progress, retention
            $table->string('payment_method')->nullable(); // cash, gcash, bank transfer
            $table->string('reference_number')->nullable();

            $table->decimal('amount', 12, 2)->default(0);
            $table->date('payment_date');
            $table->string('status')->default('confirmed'); // pending, confirmed, rejected

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};