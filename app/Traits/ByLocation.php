<?php
declare(strict_types=1);

namespace App\Traits;

use App\Models\Language;
use Cache;
use DB;

/**
 * @property string|null $language
 */
trait ByLocation
{
    public function getShopIds(array $filter): array
    {
        $regionId   = $filter['region_id']  ?? null;
        $countryId  = $filter['country_id'] ?? null;
        $cityId     = $filter['city_id']    ?? null;
        $areaId     = $filter['area_id']    ?? null;

        $byLocation = $regionId || $countryId || $cityId || $areaId;

        if (!$byLocation) {
            return [];
        }

        $key = 'shop_locations';

        switch (true) {
            case !empty($regionId):
                $key .= "_$regionId";
                break;
            case !empty($countryId):
                $key .= "_$countryId";
                break;
            case !empty($cityId):
                $key .= "_$cityId";
                break;
            case !empty($areaId):
                $key .= "_$areaId";
                break;
        }

        return Cache::remember($key,31536000, function () use ($regionId, $countryId, $cityId, $areaId) {
            return DB::table('shop_locations')
                ->where('region_id', $regionId)
                ->when($countryId, fn($q) => $q->where('country_id', $countryId), !empty($regionId)  && !request()->is('api/v1/rest/*') ? fn($q) => $q->whereNull('country_id') : fn($q) => $q)
                ->when($cityId,    fn($q) => $q->where('city_id', $cityId),       !empty($countryId) && !request()->is('api/v1/rest/*') ? fn($q) => $q->whereNull('city_id')    : fn($q) => $q)
                ->when($areaId,    fn($q) => $q->where('area_id', $areaId),       !empty($areaId)    && !request()->is('api/v1/rest/*') ? fn($q) => $q->whereNull('area_id')    : fn($q) => $q)
                ->pluck('shop_id')
                ->unique()
                ->values()
                ->toArray();
        });
    }

    public function search(mixed $query, ?string $search): array
    {
        return $query->where(function ($query) use ($search) {
            $query
                ->whereHas('region.translation', function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%$search%");
                })
                ->orWhereHas('country.translation', function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%$search%");
                })
                ->orWhereHas('city.translation', function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%$search%");
                })
                ->orWhereHas('area.translation', function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%$search%");
                });
        });
    }

    public function getWith(): array
    {
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        if (!isset($this->language)) {
            $this->language = $locale;
        }

        return [
            'region.translation' => fn($query) => $query
                ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
            'country.translation' => fn($query) => $query
                ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
            'city.translation' => fn($query) => $query
                ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
            'area.translation' => fn($query) => $query
                ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                })),
        ];
    }
}
