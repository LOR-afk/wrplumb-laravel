<?php

namespace App\Services;

use App\Models\InspectorAvailability;
use App\Models\JobOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class InspectorWorkloadService
{
    public function busyWorkerIds(): array
    {
        return JobOrder::query()
            ->where('status', 'in_progress')
            ->whereNotNull('worker_id')
            ->distinct()
            ->pluck('worker_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function isBusy(int $workerId, ?int $exceptJobOrderId = null): bool
    {
        return JobOrder::query()
            ->where('worker_id', $workerId)
            ->where('status', 'in_progress')
            ->when(
                $exceptJobOrderId,
                fn ($query) => $query->where('id', '!=', $exceptJobOrderId)
            )
            ->exists();
    }

    public function availableWorkersQuery(?string $date = null): Builder
    {
        $busyWorkerIds = $this->busyWorkerIds();

        return User::query()
            ->whereIn('role', ['inspector', 'worker'])
            ->where('is_active', true)
            ->when(
                !empty($busyWorkerIds),
                fn ($query) => $query->whereNotIn('id', $busyWorkerIds)
            )
            ->when($date, function ($query) use ($date) {
                $query->whereHas('inspectorAvailabilities', function ($availability) use ($date) {
                    $availability
                        ->whereDate('availability_date', $date)
                        ->where('status', 'available');
                });
            });
    }

    public function markOnDuty(int $workerId): void
    {
        InspectorAvailability::query()
            ->where('inspector_id', $workerId)
            ->whereDate('availability_date', now()->toDateString())
            ->where('status', 'available')
            ->update(['status' => 'on_duty']);
    }

    public function markAvailableIfFree(int $workerId): void
    {
        if ($this->isBusy($workerId)) {
            return;
        }

        InspectorAvailability::query()
            ->where('inspector_id', $workerId)
            ->whereDate('availability_date', now()->toDateString())
            ->where('status', 'on_duty')
            ->update(['status' => 'available']);
    }
}
