<?php
declare(strict_types=1);

namespace App\Repositories\ShopRepository;

use App\Helpers\Utility;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopTag;
use App\Models\Stock;
use App\Repositories\CoreRepository;
use App\Traits\ByLocation;
use Cache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ShopRepository extends CoreRepository
{
    use ByLocation;

    protected function getModelClass(): string
    {
        return Shop::class;
    }

    private function with(): array
    {
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        return [
            'translation' => fn($query) => $query
                ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
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
            'discounts' => fn($q) => $q->where('end', '>=', now())->where('active', 1)
                ->select('id', 'shop_id', 'end', 'active'),
            'shopPayments:id,payment_id,shop_id,status,client_id,secret_id',
            'shopPayments.payment:id,tag,input,sandbox,active',
            'socials',
            'locations' => fn($q) => $q->with($this->getWith())
        ];
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

                    $query->when(data_get($filter, 'not_lang'),
                        fn($q, $notLang) => $q->where('locale', '!=', data_get($filter, 'not_lang')),
                        fn($query) => $query->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                            $q->where('locale', $this->language)->orWhere('locale', $locale);
                        })),
                    );

                },
                'closedDates',
                'workingDays' => fn($q) => $q->when(data_get($filter, 'work_24_7'),
                    fn($b) => $b->where('from', '01-00')->where('to', '>=', '23-00')
                ),
            ])
            ->whereHas('translation', function ($query) use ($filter, $locale) {

                $query->when(data_get($filter, 'not_lang'),
                    fn($q, $notLang) => $q->where('locale', '!=', data_get($filter, 'not_lang')),
                    fn($query) => $query->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                        $q->where('locale', $this->language)->orWhere('locale', $locale);
                    })),
                );

            })
            ->when(data_get($filter, 'prices'), function (Builder $q, $prices) {

                $to   = data_get($prices, 0, 0) / $this->currency();
                $from = data_get($prices, 1, 0) / $this->currency();

                $q->whereHas('products', fn($q) => $q->where([
                    ['price', '>=', $to],
                    ['price', '<=', $to >= $from ? Product::max('price') : $from],
                ]));

            })
            ->select([
                'id',
                'uuid',
                'logo_img',
                'background_img',
                'status',
                'type',
                'delivery_time',
                'delivery_type',
                'open',
                'visibility',
                'verify',
                'r_count',
                'r_avg'
            ])
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * Get one Shop by UUID
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function selectPaginate(array $filter): LengthAwarePaginator
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
                }))
                    ->select('id', 'locale', 'title', 'shop_id'),
            ])
            ->whereHas(
                'translation',
                fn($query) => $query->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                }))
            )
            ->select([
                'id',
                'uuid',
                'logo_img'
            ])
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param string $uuid
     * @return Model|Builder|null
     */
    public function shopDetails(string $uuid): Model|Builder|null
    {
        $shop = Shop::where('uuid', $uuid)->first();

        if (empty($shop) || $shop->uuid !== $uuid) {
            $shop = Shop::where('id', (int)$uuid)->first();
        }

        return $shop->fresh($this->with());
    }

    /**
     * @param string $slug
     * @return Model|Builder|null
     */
    public function shopDetailsBySlug(string $slug): Model|Builder|null
    {
        /** @var Shop $shop */
        $shop   = $this->model();

        return $shop->with($this->with())
            ->where(fn($q) => $q->where('slug', $slug))
            ->first();
    }

    /**
     * @return Collection|array
     */
    public function takes(): Collection|array
    {
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        return ShopTag::with([
                'translation' => fn($query) => $query
                    ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
            ])
            ->get();
    }

    /**
     * @return mixed
     */
    public function productsAvgPrices(): mixed
    {
        return Cache::remember('products-avg-prices', 86400, function () {

            $min = Stock::where('price', '>=', 0)
                ->where('quantity', '>', 0)
                ->whereHas('product', fn($q) => $q->actual($this->language))
                ->min('price');

            $max = Stock::where('price', '>=', 0)
                ->where('quantity', '>', 0)
                ->whereHas('product', fn($q) => $q->actual($this->language))
                ->max('price');

            return [
                'min' => $min * $this->currency(),
                'max' => ($min === $max ? $max + 1 : $max) * $this->currency(),
            ];
        });
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function shopsSearch(array $filter): LengthAwarePaginator
    {
        /** @var Shop $shop */
        $shop   = $this->model();

        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        return $shop
            ->filter($filter)
            ->with([
                'translation' => fn($query) => $query
                    ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
                'discounts' => fn($q) => $q->where('end', '>=', now())->where('active', 1)
                    ->select('id', 'shop_id', 'end', 'active'),
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

    /**
     * @param array $filter
     * @return mixed
     */
    public function shopsByIDs(array $filter): mixed
    {
        /** @var Shop $shop */
        $shop   = $this->model();
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        return $shop->with([
            'translation' => fn($query) => $query->where(
                fn($q) => $q->where('locale', $this->language)->orWhere('locale', $locale)
            ),
            'discounts' => fn($q) => $q
                ->where('end', '>=', now())
                ->where('active', 1)
                ->select('id', 'shop_id', 'end', 'active'),
            'tags:id,img',
            'tags.translation' => fn($query) => $query->where(
                fn($q) => $q->where('locale', $this->language)->orWhere('locale', $locale)
            ),
        ])
            ->when(data_get($filter, 'status'), fn($q, $status) => $q->where('status', $status))
            ->find(data_get($filter, 'shops', []));
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function categories(array $filter): LengthAwarePaginator
    {
        $shopId = data_get($filter, 'shop_id');
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        return Category::where([
            ['type', Category::MAIN],
            ['active', true],
        ])
            ->with([
                'translation' => fn($q) => $q
                    ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                }))
                    ->select('id', 'locale', 'title', 'category_id'),
            ])
            ->whereHas(
                'translation',
                fn($query) => $query->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
            )
            ->whereHas('products', fn($q) => $q
                ->where('active', true)
                ->where('status', Product::PUBLISHED)
                ->where('shop_id', $shopId)
            )
            ->select([
                'id',
                'uuid',
                'keywords',
                'type',
                'active',
                'img',
            ])
            ->orderBy('id')
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param int $id
     * @return array
     */
    public function reviewsGroupByRating(int $id): array
    {
        return Utility::reviewsGroupRating([
            'reviewable_type' => Shop::class,
            'assignable_type' => Shop::class,
            'assignable_id'   => $id,
        ]);
    }
}
