<?php
declare(strict_types=1);

namespace App\Services\ShopServices;

use App\Helpers\FileHelper;
use App\Helpers\ResponseError;
use App\Models\Invitation;
use App\Models\Language;
use App\Models\Order;
use App\Models\Settings;
use App\Models\Shop;
use App\Models\User;
use App\Services\CoreService;
use App\Traits\SetTranslations;
use DB;
use Exception;
use Throwable;

class ShopService extends CoreService
{
    use SetTranslations;

    protected function getModelClass(): string
    {
        return Shop::class;
    }

    /**
     * Create a new Shop model.
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        try {
            /** @var Shop $shop */
            $shop = DB::transaction(function () use($data) {

                /** @var Shop $shop */
                $shop = $this->model()->create($this->setShopParams($data));

                $this->setTranslations($shop, $data, true, true);

                if (data_get($data, 'images.0')) {
                    $shop->update([
                        'logo_img'       => data_get($data, 'images.0'),
                        'background_img' => data_get($data, 'images.1'),
                    ]);
                    $shop->uploads(data_get($data, 'images'));
                }

                if (data_get($data, 'tags.0')) {
                    $shop->tags()->sync(data_get($data, 'tags', []));
                }

                return $shop;
            });

            $locale = Language::languagesList()->where('default', 1)->first()?->locale;

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $shop->load([
                    'translation' => fn($query) => $query->where(function ($q) use ($locale) {
                        $q->where('locale', $this->language)->orWhere('locale', $locale);
                    }),
                    'subscription',
                    'seller.roles',
                    'tags.translation' => fn($query) => $query->where(function ($q) use ($locale) {
                        $q->where('locale', $this->language)->orWhere('locale', $locale);
                    }),
                    'seller' => fn($q) => $q->select('id', 'firstname', 'lastname', 'uuid'),
                ])
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return [
                'status'    => false,
                'code'      => ResponseError::ERROR_501,
                'message'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Update specified Shop model.
     * @param string $uuid
     * @param array $data
     * @return array
     */
    public function update(string $uuid, array $data): array
    {
        try {
            $shop = $this->model()
                ->with(['invitations'])
                ->when(data_get($data, 'user_id') && !request()->is('api/v1/dashboard/admin/*'), fn($q, $userId) => $q->where('user_id', $data['user_id']))
                ->where('uuid', $uuid)
                ->first();

            if (empty($shop)) {
                return ['status' => false, 'code' => ResponseError::ERROR_404];
            }

            /** @var Shop $parent */
            /** @var Shop $shop */
            $shop->update($this->setShopParams($data, $shop));

            if ($shop->delivery_type === Shop::DELIVERY_TYPE_IN_HOUSE) {
                Invitation::whereHas('user.roles', fn($q) => $q->where('name', 'deliveryman'))
                    ->where([
                        'shop_id' => $shop->id
                    ])
                    ->delete();
            }

            $this->setTranslations($shop, $data, true, true);

            if (data_get($data, 'images.0')) {
                $shop->galleries()->delete();
                $shop->update([
                    'logo_img'       => data_get($data, 'images.0'),
                    'background_img' => data_get($data, 'images.1'),
                ]);
                $shop->uploads(data_get($data, 'images'));
            }

            if (data_get($data, 'tags.0')) {
                $shop->tags()->sync(data_get($data, 'tags', []));
            }

            $locale = Language::languagesList()->where('default', 1)->first()?->locale;

            return [
                'status' => true,
                'code' => ResponseError::NO_ERROR,
                'data' => Shop::with([
                    'translation' => fn($query) => $query->where(function ($q) use ($locale) {
                        $q->where('locale', $this->language)->orWhere('locale', $locale);
                    }),
                    'subscription',
                    'seller.roles',
                    'tags.translation' => fn($query) => $query->where(function ($q) use ($locale) {
                        $q->where('locale', $this->language)->orWhere('locale', $locale);
                    }),
                    'seller' => fn($q) => $q->select('id', 'firstname', 'lastname', 'uuid'),
                    'workingDays',
                    'closedDates',
                ])->find($shop->id)
            ];
        } catch (Exception $e) {
            return [
                'status'  => false,
                'code'    => $e->getCode() ? 'ERROR_' . $e->getCode() : ResponseError::ERROR_400,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete Shop model.
     * @param array|null $ids
     * @return array
     */
    public function delete(?array $ids = []): array
    {

        foreach (Shop::with(['orders.pointHistories'])->whereIn('id', is_array($ids) ? $ids : [])->get() as $shop) {

            /** @var Shop $shop */

            FileHelper::deleteFile($shop->logo_img);
            FileHelper::deleteFile($shop->background_img);

            if (!$shop->seller?->hasRole('admin')) {
                $shop->seller->syncRoles('user');
            }

            foreach ($shop->orders as $order) {
                /** @var Order $order */
                $order->pointHistories()->delete();
            }

            $shop->delete();

        }

        return ['status' => true, 'code' => ResponseError::NO_ERROR];
    }

    /**
     * Set params for Shop to update or create model.
     * @param array $data
     * @param Shop|null $shop
     * @return array
     */
    private function setShopParams(array $data, ?Shop $shop = null): array
    {

        $from = data_get($shop?->delivery_time, 'from', '0');
        $to   = data_get($shop?->delivery_time, 'to', '0');
        $type = data_get($shop?->delivery_time, 'type', Shop::DELIVERY_TIME_MINUTE);

        $deliveryTime = [
            'from'  => data_get($data, 'delivery_time_from', $from),
            'to'    => data_get($data, 'delivery_time_to',   $to),
            'type'  => data_get($data, 'delivery_time_type', $type),
        ];

        /** @var User $user */
        $user           = auth('sanctum')->user();
        $defaultUserId  = !$user->hasRole('admin') ? $user->id : null;

        $type = 1;// data_get(Shop::TYPES_BY, data_get($data, 'type', $shop?->type ?? 'shop'), 'shop');
        $deliveryType = data_get($data, 'delivery_type');

        $location = data_get($data, 'lat_long', $shop?->lat_long);

        $latLong = [
            'latitude'  => data_get($location, 'latitude',  data_get($shop?->lat_long, 'latitude', 0)),
            'longitude' => data_get($location, 'longitude', data_get($shop?->lat_long, 'longitude', 0)),
        ];

        return [
            'user_id'           => data_get($data, 'user_id', $defaultUserId),
            'tax'               => data_get($data, 'tax', $shop?->tax),
            'percentage'        => data_get($data, 'percentage', $shop?->percentage ?? 0),
            'min_amount'        => data_get($data, 'min_amount', $shop?->min_amount ?? 0),
            'phone'             => data_get($data, 'phone'),
            'open'              => data_get($data, 'open', $shop?->open ?? 0),
            'delivery_time'     => $deliveryTime,
            'visibility'        => (int)Settings::where('key', 'by_subscription')->first()?->value,
            'status_note'       => data_get($data, 'status_note', $shop?->status_note ?? ''),
            'type'              => $type,
            'delivery_type'     => !empty($deliveryType) ? $deliveryType : 1,
            'verify'            => data_get($data, 'verify', $shop?->verify ?? 0),
            'lat_long'          => $latLong,
        ];
    }

    /**
     * @param string $uuid
     * @param array $data
     * @return array
     */
    public function imageDelete(string $uuid, array $data): array
    {
        $shop = Shop::firstWhere('uuid', $uuid);

        if (empty($shop)) {
            return [
                'status' => false,
                'code'   => ResponseError::ERROR_404,
                'data'   => $shop->refresh(),
            ];
        }

        $tag = data_get($data, 'tag');

        $shop->galleries()
            ->where('path', $tag === 'background' ? $shop->background_img : $shop->logo_img)
            ->delete();

        $shop->update([data_get($data, 'tag') . '_img' => null]);

        return [
            'status' => true,
            'code'   => ResponseError::NO_ERROR,
            'data'   => $shop->refresh(),
        ];
    }
}
