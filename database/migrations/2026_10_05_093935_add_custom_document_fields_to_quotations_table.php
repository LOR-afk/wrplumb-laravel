
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'quotation_format',
            'project_name',
            'project_location',
            'subject',
            'custom_document_path',
            'custom_document_name',
            'custom_document_mime',
            'custom_document_size',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('quotations', $column)) {
                continue;
            }

            Schema::table('quotations', function (Blueprint $table) use ($column) {
                match ($column) {
                    'quotation_format' =>
                        $table->string('quotation_format', 20)
                            ->default('standard')->after('status'),

                    'project_name' =>
                        $table->string('project_name')
                            ->nullable()->after('quotation_format'),

                    'project_location' =>
                        $table->text('project_location')
                            ->nullable()->after('project_name'),

                    'subject' =>
                        $table->string('subject')
                            ->nullable()->after('project_location'),

                    'custom_document_path' =>
                        $table->string('custom_document_path')
                            ->nullable()->after('subject'),

                    'custom_document_name' =>
                        $table->string('custom_document_name')
                            ->nullable()->after('custom_document_path'),

                    'custom_document_mime' =>
                        $table->string('custom_document_mime')
                            ->nullable()->after('custom_document_name'),

                    'custom_document_size' =>
                        $table->unsignedBigInteger('custom_document_size')
                            ->nullable()->after('custom_document_mime'),
                };
            });
        }
    }

    public function down(): void
    {
        // This migration may run against a database where some
        // columns already existed before the migration.
        // Avoid deleting pre-existing production data on rollback.
        // Any future removal should use a separate reviewed migration.
    }
};
