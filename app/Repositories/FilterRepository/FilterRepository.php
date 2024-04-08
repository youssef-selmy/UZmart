<?php
declare(strict_types=1);

namespace App\Repositories\FilterRepository;

use App\Jobs\CashingFilter;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\Shop;
use App\Repositories\CoreRepository;
use App\Traits\SetCurrency;
use Cache;
use Illuminate\Support\Facades\Schema;

class FilterRepository extends CoreRepository
{
    use SetCurrency;

    protected function getModelClass(): string
    {
        return Product::class;
    }

    private function generateKey(array $filter): string
    {
        $key = 'products_filter_';

        foreach ($filter as $value) {
            $key .= is_array($value) ? implode('_', $value) : $value;
        }

        return $key;
    }

    public function filter(array $filter): array
    {
        $key = $this->generateKey($filter);

        $firstData  = Cache::get($key);

        if ($firstData) {
            return $firstData;
        }

        $secondData = Cache::get("{$key}_2");

        if ($secondData) {

            CashingFilter::dispatchAfterResponse($key, $filter);

            return $secondData;
        }

        return $this->cachingFilter($key, $filter);
    }

    public function cachingFilter(string $key, array $filter) {

        return Cache::remember($key, 1800, function () use ($filter, $key) {

            $locale     = Language::languagesList()->where('default', 1)->first()?->locale;
            $lang       = data_get($filter, 'lang', $locale);
            $type       = data_get($filter, 'type');
            $column     = data_get($filter, 'column', 'id');

            $shops      = [];
            $brands     = [];
            $categories = [];
            $extras     = [];

            $min        = 0;
            $max        = 0;

            if (!Schema::hasColumn('products', $column)) {
                $column = 'id';
            }

            if ($type === 'news_letter') {
                $column = 'created_at';
            }

            if ($type === 'most_sold') {
                $column = 'od_count';
            }

            $products = Product::filter($filter)
                ->actual($this->language)
                ->with([
                    'brand:id,title,img,slug',
                    'category:id,img,slug',
                    'category.translation' => fn($q) => $q
                        ->where(function ($q) use($lang, $locale) {
                            $q->where('locale', $lang)->orWhere('locale', $locale);
                        })
                        ->select([
                            'id',
                            'category_id',
                            'locale',
                            'title',
                        ]),
                    'shop:id,slug',
                    'shop.translation' => fn($q) => $q
                        ->where(function ($q) use($lang, $locale) {
                            $q->where('locale', $lang)->orWhere('locale', $locale);
                        })
                        ->select([
                            'id',
                            'shop_id',
                            'locale',
                            'title',
                        ]),
                    'stocks' => fn($q) => $q->where('quantity', '>', 0),
                    'stocks.stockExtras' => fn($q) => $q->with([
                        'group:id,type',
                        'value:id,value',
                        'group.translation' => fn($q) => $q
                            ->where(function ($q) use($lang, $locale) {
                                $q->where('locale', $lang)->orWhere('locale', $locale);
                            })
                            ->select([
                                'id',
                                'extra_group_id',
                                'locale',
                                'title',
                            ]),
                    ]),
                ])
                ->select([
                    'id',
                    'slug',
                    'active',
                    'status',
                    'category_id',
                    'brand_id',
                    'shop_id',
                    'brand_id',
                    'min_price',
                    'max_price',
                    'r_avg',
                    'age_limit',
                    'od_count',
                ])
                ->when($type !== 'category', function ($query) {
                    $query->limit(1000);
                })
                ->when($column, function ($query) use($column, $filter) {
                    $query->orderBy($column, data_get($filter, 'sort', 'desc'));
                })
                ->lazy();

            foreach ($products as $product) {

                /** @var Product $product */
                $shop     = $product->shop;
                $brand    = $product->brand;
                $category = $product->category;
                $stocks   = $product->stocks;

                if ($shop?->id && $shop?->translation?->title) {
                    $shops[$shop->id] = [
                        'id'    => $shop->id,
                        'slug'  => $shop->slug,
                        'title' => $shop->translation?->title
                    ];
                }

                if ($brand?->id && $brand?->title) {
                    $brands[$brand->id] = [
                        'id'    => $brand->id,
                        'slug'  => $brand->slug,
                        'img'   => $brand->img,
                        'title' => $brand->title,
                    ];
                }

                if ($category?->id && $category?->translation?->title) {
                    $categories[$category->id] = [
                        'id'    => $category->id,
                        'slug'  => $category->slug,
                        'img'   => $category->img,
                        'title' => $category->translation->title
                    ];
                }

                foreach ($stocks as $stock) {

                    foreach ($stock->stockExtras as $stockExtra) {

                        $value = $stockExtra->value;
                        $group = $stockExtra->group;

                        if (!$group?->id || !$value?->id) {
                            continue;
                        }

                        if (data_get($extras, $group->id)) {

                            $extras[$group->id]['extras'][$value->id] = [
                                'id'    => $value->id,
                                'value' => $value->value
                            ];

                            continue;
                        }

                        $extras[$group->id] = [
                            'id'     => $group->id,
                            'type'   => $group->type,
                            'title'  => $group->translation?->title,
                            'extras' => [
                                $value->id => [
                                    'id'    => $value->id,
                                    'value' => $value->value
                                ]
                            ]
                        ];

                    }

                }

                if ($product->min_price < $min || $min == 0) {
                    $min = $product->min_price;
                }

                if ($product->max_price > $max || $max == 0) {
                    $max = $product->max_price;
                }

            }

            $groups = collect($extras)->map(function (array $items) {

                $items['extras'] = collect(data_get($items, 'extras', []))->sortDesc()->values()->toArray();

                return $items;
            })
                ->values()
                ->toArray();

            $categories = collect($categories)->sortDesc()->values()->toArray();

            $result = [
                'shops'       => collect($shops)->sortDesc()->values()->toArray(),
                'brands'      => collect($brands)->sortDesc()->values()->toArray(),
                'categories'  => $categories,
                'group'       => $groups,
                'price'       => [
                    'min' => ($products->min('min_price') ?? 0) * $this->currency(),
                    'max' => ($products->max('max_price') ?? 0) * $this->currency(),
                ],
                'count' => $products->count(),
            ];

            Cache::remember("{$key}_2", 1860, fn() => $result);

            return $result;
        });
    }

    public function search(array $filter): array
    {
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;
        $search = data_get($filter, 'search');

        $productValues  = [];
        $categoryValues = [];
        $shopValues     = [];

        $productColumn  = data_get($filter, 'p_column', 'od_count');
        $categoryColumn = data_get($filter, 'c_column', 'id');
        $brandColumn    = data_get($filter, 'b_column', 'id');
        $shopColumn     = data_get($filter, 's_column', 'od_count');

        if (!Schema::hasColumn('products', $productColumn)) {
            $productColumn = 'od_count';
        }

        if (!Schema::hasColumn('categories', $categoryColumn)) {
            $categoryColumn = 'id';
        }

        if (!Schema::hasColumn('brands', $brandColumn)) {
            $brandColumn = 'id';
        }

        if (!Schema::hasColumn('shops', $shopColumn)) {
            $shopColumn = 'id';
        }

        $products = Product::whereHas('translation', fn($q) => $q
                ->select(['id', 'product_id', 'locale', 'title'])
                ->where('title', 'LIKE', "%$search%")
                ->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })
            )
            ->actual($this->language)
            ->select([
                'id',
                'active',
                'status',
                'digital',
                'shop_id',
            ])
            ->orderBy($productColumn, data_get($filter, 'p_sort', 'desc'))
            ->paginate(5)
            ->items();

        $categories = Category::whereHas('translation', fn($q) => $q
                ->select(['id', 'category_id', 'locale', 'title'])
                ->where('title', 'LIKE', "%$search%")
                ->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })
            )
            ->where('active', true)
            ->orderBy($categoryColumn, data_get($filter, 'c_sort', 'desc'))
            ->paginate(5)
            ->items();

        $brands = Brand::select(['id', 'title', 'active'])
            ->where('title', 'LIKE', "%$search%")
            ->where('active', true)
            ->orderBy($brandColumn, data_get($filter, 'b_sort', 'desc'))
            ->paginate(5)
            ->items();

        $shops = Shop::whereHas(
            'translation',
            fn($q) => $q
                ->select(['id', 'shop_id', 'locale', 'title'])
                ->where('title', 'LIKE', "%$search%")
                ->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })
            )
            ->where('status', Shop::APPROVED)
            ->orderBy($shopColumn, data_get($filter, 's_sort', 'desc'))
            ->paginate(5)
            ->items();

        foreach ($products as $product) {
            /** @var Product $product */
            $productValues[] = [
                'id'    => $product->id,
                'title' => $product->translation?->title
            ];
        }

        foreach ($categories as $category) {
            /** @var Category $category */
            $categoryValues[] = [
                'id'    => $category->id,
                'title' => $category->translation?->title
            ];
        }

        foreach ($shops as $shop) {
            /** @var Shop $shop */
            $shopValues[] = [
                'id'    => $shop->id,
                'title' => $shop->translation?->title
            ];
        }

        return [
            'products'   => $productValues,
            'categories' => $categoryValues,
            'brands'     => $brands,
            'shops'      => $shopValues,
        ];
    }

}
