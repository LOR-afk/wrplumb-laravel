<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('quotation_request_id')
                ->constrained('quotation_requests')
                ->cascadeOnDelete();

            $table->foreignId('inspector_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('report_no')->unique();

            $table->text('findings');
            $table->text('recommendations')->nullable();

            $table->text('client_visible_notes')->nullable();
            $table->text('internal_notes')->nullable();

            $table->decimal('estimated_material_cost', 12, 2)
                ->default(0);

            $table->decimal('estimated_labor_cost', 12, 2)
                ->default(0);

            $table->decimal('estimated_miscellaneous_cost', 12, 2)
                ->default(0);

            $table->decimal('estimated_total_cost', 12, 2)
                ->default(0);

            $table->string('status')->default('draft');

            $table->timestamp('inspection_started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('review_notes')->nullable();

            $table->timestamps();

            $table->unique(
                ['quotation_request_id', 'inspector_id'],
                'inspection_request_inspector_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_reports');
    }
};