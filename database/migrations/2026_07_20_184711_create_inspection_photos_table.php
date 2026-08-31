<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_photos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inspection_report_id')
                ->constrained('inspection_reports')
                ->cascadeOnDelete();

            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('category');
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('caption', 500)->nullable();

            $table->timestamps();

            $table->index([
                'inspection_report_id',
                'category',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_photos');
    }
};