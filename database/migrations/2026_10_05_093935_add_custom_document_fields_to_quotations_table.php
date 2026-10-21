<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('quotation_format', 20)->default('standard')->after('status');
            $table->string('project_name')->nullable()->after('quotation_format');
            $table->text('project_location')->nullable()->after('project_name');
            $table->string('subject')->nullable()->after('project_location');
            $table->string('custom_document_path')->nullable()->after('subject');
            $table->string('custom_document_name')->nullable()->after('custom_document_path');
            $table->string('custom_document_mime')->nullable()->after('custom_document_name');
            $table->unsignedBigInteger('custom_document_size')->nullable()->after('custom_document_mime');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn([
                'quotation_format',
                'project_name',
                'project_location',
                'subject',
                'custom_document_path',
                'custom_document_name',
                'custom_document_mime',
                'custom_document_size',
            ]);
        });
    }
};