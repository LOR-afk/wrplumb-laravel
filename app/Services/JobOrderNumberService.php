<?php

namespace App\Services;

use App\Models\JobOrder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class JobOrderNumberService
{
    public function createForRequest(int $requestId, string $jobType, array $attributes): JobOrder
    {
        if (!in_array($jobType, ['inspection', 'service'], true)) {
            throw new InvalidArgumentException('Invalid job order type.');
        }

        $year = now()->format('Y');
        $prefix = ($jobType === 'inspection' ? 'IJO' : 'SJO') . '-' . $year . '-';
        $lockName = 'wrplumb:joborder:' . $jobType . ':' . $year;
        $connection = DB::connection();

        if ($connection->getDriverName() !== 'mysql') {
            throw new RuntimeException('Job order numbering requires the configured MySQL database.');
        }

        $lock = $connection->selectOne('SELECT GET_LOCK(?, 10) AS acquired', [$lockName]);

        if ((int) ($lock->acquired ?? 0) !== 1) {
            throw new RuntimeException('Could not reserve a job order number. Please try again.');
        }

        try {
            return $connection->transaction(function () use ($requestId, $jobType, $attributes, $prefix) {
                $existing = JobOrder::query()
                    ->where('quotation_request_id', $requestId)
                    ->where('job_type', $jobType)
                    ->first();

                if ($existing) {
                    return $existing;
                }

                $lastRow = JobOrder::query()
                    ->where('job_order_no', 'like', $prefix . '%')
                    ->selectRaw('MAX(CAST(SUBSTRING_INDEX(job_order_no, ?, -1) AS UNSIGNED)) AS last_sequence', ['-'])
                    ->first();

                $sequence = ((int) ($lastRow?->last_sequence ?? 0)) + 1;
                $jobOrderNo = $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

                return JobOrder::create(array_merge($attributes, [
                    'quotation_request_id' => $requestId,
                    'job_type' => $jobType,
                    'job_order_no' => $jobOrderNo,
                ]));
            });
        } finally {
            $connection->selectOne('SELECT RELEASE_LOCK(?) AS released', [$lockName]);
        }
    }
}
