<?php
declare(strict_types=1);

namespace App\Repositories\CityRepository;

use App\Models\City;
use App\Models\Language;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Schema;

class CityRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return City::class;
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function paginate(array $filter): LengthAwarePaginator
    {
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        $column = data_get($filter, 'column', 'id');
        $sort   = data_get($filter, 'sort', 'desc');

        if (!Schema::hasColumn('cities', $column)) {
            $column = 'id';
        }

        return City::filter($filter)
            ->with([
                'area',
                'translation' => fn($query) => $query->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
            ])
            ->when(data_get($filter, 'city_id'), function ($query, $id) use ($sort) {
                $query->orderByRaw(DB::raw("FIELD(id, $id) $sort"));
            },
                fn($q) => $q->orderBy($column, $sort)
            )
            ->paginate(data_get($filter, 'perPage', 10));
    }

    public function show(City $model): City
    {
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        return $model->load([
            'region.translation' => fn($query) => $query->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                $q->where('locale', $this->language)->orWhere('locale', $locale);
            })),
            'country.translation' => fn($query) => $query->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                $q->where('locale', $this->language)->orWhere('locale', $locale);
            })),
            'translation' => fn($query) => $query->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                $q->where('locale', $this->language)->orWhere('locale', $locale);
            })),
            'translations',
        ]);
    }

}