<?php
declare(strict_types=1);

namespace App\Repositories\ParcelOrderRepository;

use App\Models\ParcelOrder;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Pagination\Paginator;

class AdminParcelOrderRepository extends CoreRepository
{
    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return ParcelOrder::class;
    }

    /**
     * @param array $filter
     * @return Paginator
     */
    public function ordersPaginate(array $filter = []): Paginator
    {
        /** @var ParcelOrder $parcelOrder */
        $parcelOrder = $this->model();

        return $parcelOrder
            ->filter($filter)
            ->with([
                'user:id,lastname,firstname,img,email,phone',
                'deliveryman:id,lastname,firstname',
                'transaction',
                'transaction.paymentSystem:id,tag',
                'currency',
            ])
            ->orderBy(data_get($filter, 'column', 'id'), data_get($filter, 'sort', 'desc'))
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param ParcelOrder $parcelOrder
     * @return ParcelOrder
     */
    public function show(ParcelOrder $parcelOrder): ParcelOrder
    {
        return $parcelOrder
            ->loadMissing([
                'user:id,lastname,firstname,img,email,phone',
                'deliveryman:id,lastname,firstname,img,email,phone',
                'transaction',
                'transaction.paymentSystem:id,tag',
                'currency',
                'type',
                'review',
            ]);
    }
}
