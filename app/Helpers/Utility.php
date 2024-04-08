<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Models\ParcelOrderSetting;
use DB;
use Illuminate\Pagination\LengthAwarePaginator;

class Utility
{
    /* Pagination for array */
    public static function paginate($items, $perPage, $page = null, $options = []): LengthAwarePaginator
    {
        return new LengthAwarePaginator($items?->forPage($page, $perPage), $items?->count() ?? 0, $perPage, $page, $options);
    }

    /**
     * @param ParcelOrderSetting $type
     * @param float|null $km
     * @param float|null $rate
     * @return float|null
     */
    public function getParcelPriceByDistance(ParcelOrderSetting $type, ?float $km, ?float $rate): ?float
    {
        $price      = $type->special ? $type->special_price : $type->price;
        $pricePerKm = $type->special ? $type->special_price_per_km : $type->price_per_km;

        return round(($price + ($pricePerKm * $km)) * $rate, 2);
    }

    /**
     * @param array $origin, Адрес селлера (откуда)
     * @param array $destination, Адрес клиента (куда)
     * @return float|int|null
     */
    public function getDistance(array $origin, array $destination): float|int|null
    {

        if (
            !data_get($origin, 'latitude') && !data_get($origin, 'longitude') &&
            !data_get($destination, 'latitude') && !data_get($destination, 'longitude')
        ) {
            return 0;
        }

        $originLat          = $this->toRadian(data_get($origin, 'latitude'));
        $originLong         = $this->toRadian(data_get($origin, 'longitude'));
        $destinationLat     = $this->toRadian(data_get($destination, 'latitude'));
        $destinationLong    = $this->toRadian(data_get($destination, 'longitude'));

        $deltaLat           = $destinationLat - $originLat;
        $deltaLon           = $originLong - $destinationLong;

        $delta              = pow(sin($deltaLat / 2), 2);
        $cos                = cos($destinationLong) * cos($destinationLat);

        $sqrt               = ($delta + $cos * pow(sin($deltaLon / 2), 2));
        $asin               = 2 * asin(sqrt($sqrt));

        $earthRadius        = 6371;

        return (string)$asin != 'NAN' ? round($asin * $earthRadius, 2) : 1;
    }

    /**
     * @param mixed $degree
     * @return float|null
     */
    private function toRadian(mixed $degree = 0): ?float
    {
        return $degree * pi() / 180;
    }

    /**
     * @param $reviews
     * @return float[]
     */
    public static function groupRating($reviews): array
    {
        $result = [
            1 => 0.0,
            2 => 0.0,
            3 => 0.0,
            4 => 0.0,
            5 => 0.0,
        ];

        foreach ($reviews as $review) {

            $rating = (int)data_get($review, 'rating');

            if (data_get($result, $rating)) {
                $result[$rating] += data_get($review, 'count');
                continue;
            }

            $result[$rating] = data_get($review, 'count');
        }

        return $result;
    }

    /**
     * @param array $where
     * @return array
     */
    public static function reviewsGroupRating(array $where): array
    {
        $reviews = DB::table('reviews')
            ->where($where)
            ->select([
                DB::raw('count(id) as count, sum(rating) as rating, rating')
            ])
            ->groupBy(['rating'])
            ->get();

        return [
            'group' => Utility::groupRating($reviews),
            'count' => $reviews->sum('count'),
            'avg'   => round((double)$reviews->avg('rating'), 1),
        ];
    }
}
