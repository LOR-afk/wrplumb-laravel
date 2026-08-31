<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_material_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inspection_report_id')
                ->constrained('inspection_reports')
                ->cascadeOnDelete();

            $table->string('item_name');
            $table->decimal('quantity', 12, 2)->default(1);
            $table->string('unit', 50);
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);

            $table->timestamps();

            $table->index('inspection_report_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_material_items');
    }
};