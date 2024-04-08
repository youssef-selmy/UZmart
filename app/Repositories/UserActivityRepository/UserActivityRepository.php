<?php
declare(strict_types=1);

namespace App\Repositories\UserActivityRepository;

use App\Models\UserActivity;
use App\Repositories\CoreRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class UserActivityRepository extends CoreRepository
{

    protected function getModelClass(): string
    {
        return UserActivity::class;
    }

    public function paginate(array $filter = []): LengthAwarePaginator
    {
        return $this->model()
            ->filter($filter)
            ->orderBy(data_get($filter, 'column', 'id'), data_get($filter, 'sort', 'desc'))
            ->paginate(data_get($filter, 'perPage', 10));
    }
}
