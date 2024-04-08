<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Http\Resources\Bonus\BonusResource;
use App\Models\Shop;
use App\Models\User;
use Cache;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Shop|JsonResource $this */
        /** @var User $user */
        $user           = auth('sanctum')->user();
        $isSeller       = auth('sanctum')->check() && $user?->hasRole('seller');
        $isRecommended  = in_array($this->id, array_keys(Cache::get('shop-recommended-ids', [])));
        $locales        = $this->relationLoaded('translations')
            ? $this->translations->pluck('locale')->toArray()
            : null;

        return [
            'id'                => $this->when($this->id, $this->id),
            'slug'              => $this->when($this->slug, $this->slug),
            'uuid'              => $this->when($this->uuid, $this->uuid),
            'discounts_count'   => $this->whenLoaded('discounts', $this->discounts_count),
            'user_id'           => $this->when($this->user_id, $this->user_id),
            'tax'               => $this->when($this->tax, $this->tax),
            'percentage'        => $this->when($this->percentage, $this->percentage),
            'phone'             => $this->when($this->phone, $this->phone),
            'open'              => (bool)$this->open,
            'visibility'        => (bool)$this->visibility,
            'verify'            => (bool)$this->verify,
            'delivery_type'     => $this->when($this->delivery_type, $this->delivery_type),
            'background_img'    => $this->when($this->background_img, $this->background_img),
            'logo_img'          => $this->when($this->logo_img, $this->logo_img),
            'min_amount'        => $this->when($this->min_amount, $this->min_amount),
            'is_recommended'    => $this->when($isRecommended, $isRecommended),
            'status'            => $this->when($this->status, $this->status),
            'status_note'       => $this->when($this->status_note, $this->status_note),
            'delivery_time'     => $this->when($this->delivery_time, $this->delivery_time),
            'invite_link'       => $this->when($isSeller, "/shop/invitation/$this->uuid/link"),
            'rating_avg'        => $this->when($this->reviews_avg_rating, $this->reviews_avg_rating),
            'reviews_count'     => $this->when($this->reviews_count,      $this->reviews_count),
            'orders_count'      => $this->when($this->orders_count,       $this->orders_count),
            'lat_long'          => $this->when($this->lat_long,           $this->lat_long),
            'locations_count'   => $this->when($this->locations_count,    $this->locations_count),
            'r_count'           => $this->when($this->r_count,            $this->r_count),
            'r_avg'             => $this->when($this->r_avg,              $this->r_avg),
            'r_sum'             => $this->when($this->r_sum,              $this->r_sum),
            'o_count'           => $this->when($this->o_count,            $this->o_count),
            'od_count'          => $this->when($this->od_count,           $this->od_count),
            'created_at'        => $this->when($this->created_at, $this->created_at?->format('Y-m-d H:i:s') . 'Z'),
            'updated_at'        => $this->when($this->updated_at, $this->updated_at?->format('Y-m-d H:i:s') . 'Z'),
            'products_count'    => $this->whenLoaded('products', $this->products_count, 0),

            'translation'       => TranslationResource::make($this->whenLoaded('translation')),
            'tags'              => ShopTagResource::collection($this->whenLoaded('tags')),
            'translations'      => TranslationResource::collection($this->whenLoaded('translations')),
            'locales'           => $this->when($locales, $locales),
            'seller'            => UserResource::make($this->whenLoaded('seller')),
            'subscription'      => ShopSubscriptionResource::make($this->whenLoaded('subscription')),
            'categories'        => CategoryResource::collection($this->whenLoaded('categories')),
            'bonus'             => BonusResource::make($this->whenLoaded('bonus')),
            'discounts'         => SimpleDiscountResource::collection($this->whenLoaded('discounts')),
            'shop_payments'     => ShopPaymentResource::collection($this->whenLoaded('shopPayments')),
            'socials'           => ShopSocialResource::collection($this->whenLoaded('socials')),
            'shop_working_days' => ShopWorkingDayResource::collection($this->whenLoaded('workingDays')),
            'shop_closed_date'  => ShopClosedDateResource::collection($this->whenLoaded('closedDates')),
            'location'          => ShopLocationResource::make($this->whenLoaded('location')),
            'locations'         => ShopLocationResource::collection($this->whenLoaded('locations'))
        ];
    }
}
