<?php
declare(strict_types=1);

namespace App\Repositories\StoryRepository;

use App\Models\Language;
use App\Models\Product;
use App\Models\Story;
use App\Repositories\CoreRepository;
use Illuminate\Database\Eloquent\Builder;

class StoryRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return Story::class;
    }

    /**
     * @param array $data
     * @param string $type
     * @return mixed
     */
    public function index(array $data = [], string $type = 'paginate'): mixed
    {
        /** @var Story $stories */
        $stories = $this->model();

        $paginate = data_get([
            'paginate'          => 'paginate',
            'simplePaginate'    => 'simplePaginate'
        ], $type, 'paginate');

        return $stories
            ->with([
                'product'               => fn ($q) => $q->select(['id', 'uuid']),
                'product.translation'   => fn ($q) => $q->select('id', 'product_id', 'locale', 'title')
                    ->where('locale', $this->language),
                'shop'                  => fn ($q) => $q->select(['id', 'uuid', 'user_id', 'logo_img']),
                'shop.translation'      => fn ($q) => $q->select('id', 'shop_id', 'locale', 'title')
                    ->where('locale', $this->language),
            ])
            ->select([
                'id',
                'product_id',
                'shop_id',
                'active',
                'file_urls',
            ])
            ->when(data_get($data, 'shop_id'), fn(Builder $q, $shopId) => $q->where('shop_id', $shopId))
            ->when(data_get($data, 'product_id'), fn(Builder $q, $productId) => $q->where('product_id', $productId))
            ->when(isset($data['active']), fn($q) => $q->where('active', $data['active']))
            ->orderBy(data_get($data, 'column', 'id'), data_get($data, 'sort', 'desc'))
            ->$paginate(data_get($data, 'perPage', 15));
    }

    public function list(array $data = []): array
    {
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        $shopsStories = Story::with([
            'shop:id,uuid,logo_img',
            'shop.seller:id,firstname,lastname,img',
            'shop.translation' => fn ($q) => $q
                ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                }))
                ->select('id', 'shop_id', 'locale', 'title'),
            'product' => fn($q) => $q->select([
                'id',
                'shop_id',
                'uuid',
                'active',
                'status',
            ])
                ->when(data_get($data, 'free'), function ($q) {
                    $q->whereHas('stock', fn($b) => $b->where('price', '=', 0));
                })
                ->where('active', 1)
                ->where('status', Product::PUBLISHED),
            'product.translation' => fn($query) => $query
                ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                }))
                ->select('id', 'product_id', 'locale', 'title'),
        ])
            ->where('created_at', '>=', date('Y-m-d', strtotime('-1 day')))
            ->simplePaginate(data_get($data, 'perPage', 100));

        $shops = [];

        foreach ($shopsStories as $shopStories) {

            /** @var Story $shopStories */

            if (!isset($shops[$shopStories->shop_id])) {
                $shops[$shopStories->shop_id] = [];
            }

            $product = $shopStories->product;

            if ($product && (!$product->active || $product->status !== Product::PUBLISHED)) {
                continue;
            }

            foreach ($shopStories->file_urls as $fileUrl) {

                $shopsStoriesTitle  = $shopStories?->shop?->translation?->title;
                $productTitle       = $product?->translation?->title;
                $createdAt          = $shopStories?->created_at;
                $updatedAt          = $shopStories?->updated_at;

                $shops[$shopStories->shop_id][] = [
                    'shop_id'       => $shopStories->shop_id,
                    'shop_uuid'     => $shopStories?->shop?->uuid,
                    'logo_img'      => $shopStories->shop->logo_img,
                    'title'         => $shopsStoriesTitle,
                    'firstname'     => $shopStories->shop->seller?->firstname,
                    'lastname'      => $shopStories->shop->seller?->lastname,
                    'avatar'        => $shopStories->shop->seller?->img,
                    'product_uuid'  => $product?->uuid,
                    'product_title' => $productTitle,
                    'url'           => $fileUrl,
                    'created_at'    => !empty($createdAt) ? $createdAt->format('Y-m-d H:i:s') . 'Z' : null,
                    'updated_at'    => !empty($updatedAt) ? $updatedAt->format('Y-m-d H:i:s') . 'Z' : null,
                ];

            }

        }

        $shops = collect($shops);

        return $shops?->count() > 0 ? array_values($shops->reject(fn($items) => empty($items))->toArray()) : [];
    }

    public function show(Story $story): Story
    {
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        return $story->load([
            'product.translation' => fn($q) => $q
                ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
            'shop.translation'  => fn ($q) => $q
                ->select('id', 'shop_id', 'locale', 'title')
                ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
        ]);
    }
}
