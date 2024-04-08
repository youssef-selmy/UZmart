<?php
declare(strict_types=1);

namespace App\Repositories\AdsPackageRepository;

use App\Models\Language;
use App\Models\ShopAdsPackage;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Schema;

class ShopAdsPackageRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return ShopAdsPackage::class;
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function paginate(array $filter): LengthAwarePaginator
    {
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;
        $column = data_get($filter, 'column','id');

        if (!Schema::hasColumn('shop_ads_packages', $column)) {
            $column = 'id';
        }

        return ShopAdsPackage::filter($filter)
            ->with([
                'transaction',
                'shop:id',
                'shop.translation' => fn($query) => $query
                    ->select(['id', 'shop_id', 'locale', 'title'])
                    ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
                'adsPackage.translation' => fn($query) => $query
                    ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
            ])
            ->orderBy($column, data_get($filter, 'sort','desc'))
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param ShopAdsPackage $model
     * @return ShopAdsPackage
     */
    public function show(ShopAdsPackage $model): ShopAdsPackage
    {
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        return $model->loadMissing([
            'transaction',
            'shopAdsProducts.product.translation' => fn($query) => $query
                ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
            'adsPackage.translation' => fn($query) => $query
                ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
            'shop:id',
            'shop.translation' => fn($query) => $query
                ->select(['id', 'shop_id', 'locale', 'title'])
                ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
        ]);
    }

}
