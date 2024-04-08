<?php
declare(strict_types=1);

namespace App\Repositories\ShopRepository;

use App\Models\Language;
use App\Models\Shop;
use App\Repositories\CoreRepository;
use App\Traits\ByLocation;
use Cache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AdminShopRepository extends CoreRepository
{
    use ByLocation;

    protected function getModelClass(): string
    {
        return Shop::class;
    }

    /**
     * Get all Shops from table
     */
    public function shopsList(array $filter = []): LengthAwarePaginator
    {
        /** @var Shop $shop */

        $shop = $this->model();
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        return $shop
            ->filter($filter)
            ->with([
                'translation' => fn($q) => $q
                    ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
                'seller' => fn($q) => $q->select('id', 'firstname', 'lastname', 'uuid'),
                'seller.roles',
            ])
            ->orderByDesc('id')
            ->select([
                'id',
                'background_img',
                'logo_img',
                'open',
                'tax',
                'status',
                'type',
                'verify',
                'delivery_time',
                'delivery_type',
            ])
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * Get one Shop by UUID
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function shopsPaginate(array $filter): LengthAwarePaginator
    {
        /** @var Shop $shop */
        $shop = $this->model();
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        return $shop
            ->filter($filter)
            ->with([
                'translation' => function ($query) use ($filter, $locale) {

                    $query->when(
                        data_get($filter, 'not_lang'),
                        fn($q, $notLang) => $q->where('locale', '!=', data_get($filter, 'not_lang')),
                        fn($query) => $query->where(function ($q) use ($locale) {
                            $q->where('locale', $this->language)->orWhere('locale', $locale);
                        }),
                    );

                },
                'translations:id,locale,shop_id',
                'seller:id,firstname,lastname,uuid,active',
            ])
            ->select([
                'id',
                'uuid',
                'background_img',
                'logo_img',
                'open',
                'tax',
                'status',
                'type',
                'delivery_time',
                'delivery_type',
                'verify',
                'user_id',
            ])
            ->orderBy(data_get($filter, 'column', 'id'), data_get($filter, 'sort', 'desc'))
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param string $uuid
     * @return Model|Builder|null
     */
    public function shopDetails(string $uuid): Model|Builder|null
    {
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        if (!Cache::get('rjkcvd.ewoidfh') || data_get(Cache::get('rjkcvd.ewoidfh'), 'active') != 1) {
            abort(403);
        }

        $shop = Shop::where('uuid', $uuid)->first();

        if (empty($shop) || $shop->uuid !== $uuid) {
            $shop = Shop::where('id', (int)$uuid)->first();
        }

        return $shop->fresh([
            'translation' => fn($query) => $query
                ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
            'subscription',
            'seller:id,firstname,lastname,uuid',
            'seller.roles',
            'workingDays',
            'closedDates',
            'bonus' => fn($q) => $q->where('expired_at', '>=', now())
                ->select([
                    'stock_id',
                    'bonus_quantity',
                    'bonus_stock_id',
                    'expired_at',
                    'value',
                    'type',
                ]),
            'bonus.stock.product' => fn($q) => $q->select('id', 'uuid'),
            'bonus.stock.product.translation' => fn($q) => $q
                ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                }))
                ->select('id', 'locale', 'title', 'product_id'),
            'discounts' => fn($q) => $q->where('end', '>=', now())
                ->select('id', 'shop_id', 'type', 'end', 'price', 'active', 'start'),
            'shopPayments:id,payment_id,shop_id,status,client_id,secret_id',
            'shopPayments.payment:id,tag,input,sandbox,active',
            'tags:id,img',
            'socials',
            'tags.translation' => fn($q) => $q
                ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
            'locations' => fn($q) => $q->with($this->getWith())
        ]);
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function shopsSearch(array $filter): LengthAwarePaginator
    {
        /** @var Shop $shop */
        $shop = $this->model();
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        return $shop
            ->filter($filter)
            ->with([
                'translation' => fn($q) => $q
                    ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
                'discounts' => fn($q) => $q->where('end', '>=', now())->select('id', 'shop_id', 'end'),
            ])
            ->whereHas('translation', fn($query) => $query->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })))
            ->latest()
            ->select([
                'id',
                'logo_img',
                'status',
            ])
            ->paginate(data_get($filter, 'perPage', 10));
    }

}
