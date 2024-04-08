<?php
declare(strict_types=1);

namespace App\Repositories\ShopTagRepository;

use App\Models\Language;
use App\Models\ShopTag;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ShopTagRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return ShopTag::class;
    }

    public function paginate($data = []): LengthAwarePaginator
    {
        /** @var ShopTag $shopTags */
        $shopTags = $this->model();
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        return $shopTags
            ->with([
                'translation'       => fn($q) => $q
                    ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
            ])
            ->when(data_get($data, 'search'), function (Builder $query, $search) {
                $query->whereHas('translation', fn($q) => $q->where('title', 'like', "%$search%"));
            })
            ->orderBy(data_get($data, 'column', 'id'), data_get($data, 'sort', 'desc'))
            ->paginate(data_get($data, 'perPage', 10));
    }

    public function show(ShopTag $shopTag): ShopTag
    {
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        return $shopTag->loadMissing([
            'translations',
            'translation'       => fn($q) => $q
                ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
        ]);
    }
}
