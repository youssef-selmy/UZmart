<?php
declare(strict_types=1);

namespace App\Models;

use App\Traits\ByLocation;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Like
 *
 * @property int $id
 * @property string $likable_type
 * @property int $likable_id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Blog|Product|Shop|Banner|null $likable
 * @method static Builder|self filter(array $filter)
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereLikableId($value)
 * @method static Builder|self whereLikableType($value)
 * @method static Builder|self whereUpdatedAt($value)
 * @method static Builder|self whereUserId($value)
 * @mixin Eloquent
 */
class Like extends Model
{
    use HasFactory, ByLocation;

    protected $guarded = ['id'];

    const TYPES = [
        'blog'    => Blog::class,
        'product' => Product::class,
        'shop'    => Shop::class,
        'banner'  => Banner::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likable(): MorphTo
    {
        return $this->morphTo('likable');
    }

    public function scopeFilter($query, array $filter)
    {
        $query
            ->when(data_get($filter, 'type'), function($q, $type) use ($filter) {

                $type = data_get(self::TYPES, $type, Product::class);

                $q->whereHasMorph('likable', $type, function ($query) use ($type, $filter) {

                    $shopIds    = $this->getShopIds($filter);

                    $regionId   = $filter['region_id']  ?? null;
                    $countryId  = $filter['country_id'] ?? null;
                    $cityId     = $filter['city_id']    ?? null;
                    $areaId     = $filter['area_id']    ?? null;
                    $byLocation = $regionId || $countryId || $cityId || $areaId;

                    if (!$byLocation) {
                        return $query;
                    }

                    return $query
                        ->when($type === Product::class, function ($query) use ($shopIds) {
                            $query->whereIn('shop_id', $shopIds);
                        })
                        ->when($type === Shop::class, function ($query) use ($shopIds) {
                            $query->whereIn('id', $shopIds);
                        })
                        ->when($type === Banner::class, function ($query) use ($shopIds) {
                            $query->whereHas('products', fn($q) => $q->whereIn('shop_id', $shopIds));
                        });

                });

            })
            ->when(data_get($filter, 'type_id'), function($q, $typeId) {

                $q->where('likable_id', $typeId);

            })
            ->when(data_get($filter, 'type_id'), function($q, $typeId) {

                $q->where('likable_id', $typeId);

            })
            ->when(data_get($filter, 'user_id'), fn($q, $userId) => $q->where('user_id', $userId));
    }
}
