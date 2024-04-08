<?php
declare(strict_types=1);

namespace App\Repositories\DiscountRepository;

use App\Models\Discount;
use App\Models\Language;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DiscountRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return Discount::class;
    }

    public function discountsPaginate(array $filter = []): LengthAwarePaginator
    {
        return $this->model()
            ->filter($filter)
            ->orderBy(data_get($filter, 'column', 'id'), data_get($filter, 'sort','desc'))
            ->paginate(data_get($filter, 'perPage', 10));
    }

    public function discountDetails(Discount $discount): Discount
    {
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        return $discount->load([
            'galleries',
            'stocks.product.translation' => function($q) use ($locale) {
                $q
                    ->select('id', 'product_id', 'locale', 'title')
                    ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                }));
            }
        ]);
    }

}
