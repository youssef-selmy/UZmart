<?php
declare(strict_types=1);

namespace App\Repositories\BrandRepository;

use App\Models\Brand;
use App\Models\Language;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class BrandRepository extends CoreRepository
{
    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return Brand::class;
    }

    public function brandsList(array $filter = []): array|Collection|\Illuminate\Support\Collection
    {
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        return $this->model()
            ->filter($filter)
            ->with([
                'shop.translation' => fn($q) => $q
                    ->select('id', 'shop_id', 'locale', 'title')
                    ->where(function ($query) use($locale) {
                        $query->where('locale', $this->language)->orWhere('locale', $locale);
                    })
            ])
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Get brands with pagination
     */
    public function brandsPaginate(array $filter = []): LengthAwarePaginator
    {
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        return $this->model()
            ->filter($filter)
            ->withCount('products')
            ->with([
                'shop.translation' => fn($q) => $q
                    ->select('id', 'shop_id', 'locale', 'title')
                    ->where(function ($query) use($locale) {
                        $query->where('locale', $this->language)->orWhere('locale', $locale);
                    })
            ])
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param int $id
     * @return Model|null
     */
    public function brandDetails(int $id): Model|null
    {
        return $this->model()->find($id);
    }

    /**
     * @param string $slug
     * @return Model|null
     */
    public function brandDetailsBySlug(string $slug): Model|null
    {
        return $this->model()->where('slug', $slug)->first();
    }

    public function brandsSearch(array $filter = []): LengthAwarePaginator
    {
        return $this->model()
            ->withCount('products')
            ->when(data_get($filter, 'search'), fn($q, $search) => $q->where('title', 'LIKE', "%$search%"))
            ->when(isset($filter['active']), fn($q) => $q->whereActive($filter['active']))
            ->orderBy(data_get($filter,'column','id'), data_get($filter,'sort','desc'))
            ->paginate(data_get($filter, 'perPage', 10));
    }
}
