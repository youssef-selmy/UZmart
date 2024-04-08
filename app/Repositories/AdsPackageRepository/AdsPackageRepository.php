<?php
declare(strict_types=1);

namespace App\Repositories\AdsPackageRepository;

use App\Models\AdsPackage;
use App\Models\Language;
use App\Models\ShopAdsPackage;
use App\Repositories\CoreRepository;
use App\Repositories\ProductRepository\RestProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Schema;

class AdsPackageRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return AdsPackage::class;
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function index(array $filter): LengthAwarePaginator
    {
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        return AdsPackage::filter($filter)
            ->with([
                'translation' => fn($query) => $query->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
                'galleries',
            ])
            ->whereHas('shopAdsPackages', function ($q) {
                $q
                    ->where('status', ShopAdsPackage::APPROVED)
                    ->whereDate('expired_at', '>', date('Y-m-d H:i:s'));
            })
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function adsProducts(array $filter): LengthAwarePaginator
    {
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        $column = data_get($filter, 'column','id');

        if (!Schema::hasColumn('ads_packages', $column)) {
            $filter['column'] = 'id';
        }

        return AdsPackage::filter($filter)
            ->with([
                'translation' => fn($query) => $query->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
                'galleries',
                'shopAdsPackages' => function ($q) use ($filter, $locale) {
                    $q
                        ->with([
                            'shopAdsProducts.product:id,uuid,slug,img',
                            'shopAdsProducts.product.translation' => fn($query) => $query->where(function ($q) use ($locale) {
                                $q
                                    ->select(['id', 'product_id', 'locale', 'title'])
                                    ->where('locale', $this->language)
                                    ->orWhere('locale', $locale);
                            }),
                        ])
                        ->when(data_get($filter, 'shop_id'), fn($q, $id) => $q->where('shop_id', $id))
                        ->where('active', true)
                        ->where('status', ShopAdsPackage::APPROVED)
                        ->whereDate('expired_at', '>', date('Y-m-d H:i:s'));
                },
            ])
            ->whereHas('shopAdsPackages', function ($q) use ($filter) {
                $q
                    ->when(data_get($filter, 'shop_id'), fn($q, $id) => $q->where('shop_id', $id))
                    ->where('active', true)
                    ->where('status', ShopAdsPackage::APPROVED)
                    ->whereDate('expired_at', '>', date('Y-m-d H:i:s'));
            })
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function paginate(array $filter): LengthAwarePaginator
    {
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        return AdsPackage::filter($filter)
            ->with([
                'translation' => fn($query) => $query->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
                'galleries',
            ])
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param AdsPackage $model
     * @return AdsPackage
     */
    public function show(AdsPackage $model): AdsPackage
    {
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        return $model->loadMissing([
            'translation' => fn($query) => $query->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
            'translations',
            'galleries',
            'shopAdsPackages' => function ($q) {
                $q
                    ->with([
                        'shopAdsProducts.product' => fn($q) => $q->with((new RestProductRepository)->with()),
                    ])
                    ->where('active', true)
                    ->where('status', ShopAdsPackage::APPROVED)
                    ->whereDate('expired_at', '>', date('Y-m-d H:i:s'));
            }
        ]);
    }

}
