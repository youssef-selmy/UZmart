<?php
declare(strict_types=1);

namespace App\Repositories\DeliveryPointWorkingDayRepository;

use App\Models\DeliveryPoint;
use App\Models\DeliveryPointWorkingDay;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DeliveryPointWorkingDayRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return DeliveryPointWorkingDay::class;
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function paginate(array $filter = []): LengthAwarePaginator
    {
        return DeliveryPoint::with([
            'workingDays' => fn($q) => $q->select(['id', 'day', 'from', 'to', 'disabled', 'delivery_point_id'])
        ])
            ->whereHas('workingDays')
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param int $deliveryPointId
     * @return Collection
     */
    public function show(int $deliveryPointId): Collection
    {
        return DeliveryPointWorkingDay::where('delivery_point_id', $deliveryPointId)
            ->orderBy('day')
            ->get();
    }
}
