<?php
declare(strict_types=1);

namespace App\Repositories\LikeRepository;

use App\Models\Language;
use App\Models\Like;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Schema;

class LikeRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return Like::class;
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function paginate(array $filter = []): LengthAwarePaginator
    {
        /** @var Like $model */
        $model  = $this->model();
        $column = data_get($filter, 'column', 'id');

        if (!Schema::hasColumn('likes', $column)) {
            $column = 'id';
        }

        return $model
            ->filter($filter)
            ->with($this->getWithByType(data_get($filter, 'type')))
            ->orderBy($column, data_get($filter, 'sort', 'desc'))
            ->paginate(data_get($filter, 'perPage', 10));
    }


    private function getWithByType(?string $type = 'product'): array
    {
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        $with = [
            'likable'
        ];

        if ($type === 'product') {
            $with = [
                'likable.translation' => function ($q) use ($locale) {
                    $q->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                }));
                },
                'likable.stocks' => fn($q) => $q->where('quantity', '>', 0),
                'likable.stocks.gallery',
                'likable.stocks.stockExtras.value',
                'likable.stocks.stockExtras.group.translation' => function ($q) use ($locale) {
                    $q->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                }));
                },
                'likable.stocks.bonus' => fn($q) => $q
                    ->where('expired_at', '>', now())
                    ->select([
                        'id', 'expired_at', 'stock_id',
                        'bonus_quantity', 'value', 'type', 'status'
                    ]),
                'likable.stocks.discount' => fn($q) => $q
                    ->where('start', '<=', today())
                    ->where('end', '>=', today())
                    ->where('active', 1),
            ];
        }

        return $with;
    }
}
