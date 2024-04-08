<?php
declare(strict_types=1);

namespace App\Repositories\ShopLocationRepository;

use App\Models\ShopLocation;
use App\Repositories\CoreRepository;
use App\Traits\ByLocation;
use Illuminate\Pagination\LengthAwarePaginator;

class ShopLocationRepository extends CoreRepository
{
    use ByLocation;

    protected function getModelClass(): string
    {
        return ShopLocation::class;
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function paginate(array $filter): LengthAwarePaginator
    {
        return $this->model()
            ->filter($filter)
            ->with($this->getWith())
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param ShopLocation $shopLocation
     * @return ShopLocation
     */
    public function show(ShopLocation $shopLocation): ShopLocation
    {
        return $shopLocation->load($this->getWith());
    }
}
