<?php

namespace App\Console\Commands;

use App\Models\Quotation;
use App\Models\QuotationRequest;
use Illuminate\Console\Command;

class ArchiveCompletedProjects extends Command
{
    protected $signature = 'projects:archive-completed';

    protected $description = 'Archive completed projects older than 30 days';

    public function handle(): int
    {
        $cutoff = now()->subDays(30);

        $requests = QuotationRequest::whereNull('archived_at')
            ->whereNotNull('completed_at')
            ->where('completed_at', '<=', $cutoff)
            ->get();

        foreach ($requests as $request) {
            $request->forceFill([
                'archived_at' => now(),
                'archive_reason' => 'Completed project older than 30 days',
            ])->save();

            Quotation::where('quotation_request_id', $request->id)
                ->whereNull('archived_at')
                ->update([
                    'archived_at' => now(),
                    'archive_reason' => 'Related completed project archived',
                ]);
        }

        $this->info("Archived {$requests->count()} completed project(s).");

        return self::SUCCESS;
    }
}