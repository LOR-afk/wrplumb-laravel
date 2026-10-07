<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('file_path');
            $table->string('original_name');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        if (!Schema::hasColumn('quotations', 'quotation_format')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->string('quotation_format', 20)->default('standard');
            });
        }

        if (!Schema::hasColumn('quotations', 'project_name')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->string('project_name')->nullable();
            });
        }

        if (!Schema::hasColumn('quotations', 'project_location')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->text('project_location')->nullable();
            });
        }

        if (!Schema::hasColumn('quotations', 'subject')) {
            Schema::table('quotations', function (Blueprint $table) {
                $table->string('subject')->nullable();
            });
        }

        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('quotation_template_id')
                ->nullable()
                ->constrained('quotation_templates')
                ->nullOnDelete();

            $table->string('generated_document_path')->nullable();
            $table->string('generated_document_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quotation_template_id');
            $table->dropColumn([
                'generated_document_path',
                'generated_document_name',
            ]);
        });

        Schema::dropIfExists('quotation_templates');
    }
};
