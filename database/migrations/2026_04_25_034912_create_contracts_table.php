<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quotation_id')
                ->constrained('quotations')
                ->cascadeOnDelete();

            $table->string('contract_no')->unique();
            $table->string('title')->nullable();

            $table->date('contract_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->string('status')->default('generated'); // generated, sent, accepted, finalized, cancelled

            $table->string('client_name')->nullable();
            $table->text('client_address')->nullable();
            $table->text('project_address')->nullable();

            $table->text('scope_of_work')->nullable();
            $table->json('payment_terms')->nullable();
            $table->decimal('total_contract_price', 12, 2)->default(0);

            $table->text('special_terms')->nullable();

            $table->foreignId('generated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('client_accepted_at')->nullable();
            $table->timestamp('finalized_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};