<?php
declare(strict_types=1);

namespace App\Imports;

use App\Models\Shop;
use App\Traits\Loggable;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

class ShopImport implements ToCollection, WithHeadingRow, WithBatchInserts
{
    use Importable, Loggable;

    /**
     * @param Collection $collection
     * @return void
     */
    public function collection(Collection $collection)
    {
        if (!Cache::get('rjkcvd.ewoidfh') || data_get(Cache::get('rjkcvd.ewoidfh'), 'active') != 1) {
            abort(403);
        }

        foreach ($collection as $row) {

            $type = !empty(data_get($row, 'type')) ?
                data_get(Shop::TYPES_BY, data_get($row, 'type'), Shop::TYPE_SHOP) :
                Shop::TYPE_SHOP;

            $explodeDeliveryTime = explode(',', (string)data_get($row, 'delivery_time'));

            $deliveryTime = [
                'from' => str_replace(['from:', ' '], '', data_get($explodeDeliveryTime, 0)),
                'to'   => str_replace(['to:', ' '], '', data_get($explodeDeliveryTime, 1)),
                'type' => str_replace(['type:', ' '], '', data_get($explodeDeliveryTime, 2)),
            ];

            $location = explode(',', (string)data_get($row, 'location'));

            $open = false;
            if (in_array(data_get($row, 'open'), ['=TRUE', 'TRUE', '=true', 'true'])) {
                $open = true;
            }

            $shop = Shop::updateOrCreate(['user_id' => data_get($row, 'user_id')], [
                'tax'               => data_get($row, 'tax') ?? 0,
                'percentage'        => data_get($row, 'percentage') ?? 0,
                'lat_long'          => [
                    'latitude'  => data_get($location, 0),
                    'longitude' => data_get($location, 1)
                ],
                'phone'             => data_get($row, 'phone'),
                'open'              => $open,
                'background_img'    => data_get($row, 'background_img', ''),
                'logo_img'          => data_get($row, 'logo_img', ''),
                'min_amount'        => data_get($row, 'min_amount', 1),
                'status'            => data_get($row, 'status', 'new'),
                'status_note'       => data_get($row, 'status_note', ''),
                'created_at'        => data_get($row, 'created_at'),
                'delivery_time'     => $deliveryTime,
                'type'              => $type,
                'verify'            => data_get($row, 'verify', 0) ?? 0,
                'visibility'        => data_get($row, 'visibility', 1),
            ]);

            $this->downloadImages(Shop::find($shop->id), data_get($row, 'img_urls', ''));
        }

    }

    private function downloadImages(Shop $shop, ?string $imgUrls): bool
    {
        try {

            if (empty($imgUrls)) {
                return false;
            }

            $urls = explode(',', $imgUrls);

            $images  = [];
            $storage = [];

            foreach ($urls as $url) {

                if (str_contains($url, 'http://') || str_contains($url, 'https://')) {
                    $images[] = file_get_contents($url,'test');
                }

                $dir      = 'shops';

                $date     = Str::slug(Carbon::now()->format('Y-m-d h:i:s')) . Str::random(6);

                $name     = substr(strrchr(Str::replace(['https://', 'http://'], '', $url), "."), 1);

                foreach ($images as $image) {
                    $storage[] = "$dir/$date.$name";
                    Storage::disk('public')->put("images/$dir/$date.$name", $image, 'public');
                }

            }

            DB::transaction(function () use ($shop, $storage) {
                $shop->galleries()->delete();

                $shop->update(['logo_img' => data_get($storage, 0)]);
                $shop->update(['background_img' => data_get($storage, 1)]);

                $shop->uploads($storage);
            });

            return true;
        } catch (Throwable $e) {
            $this->error($e);
        }

        return false;
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function batchSize(): int
    {
        return 10;
    }
}
