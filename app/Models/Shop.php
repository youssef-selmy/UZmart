<?php
declare(strict_types=1);

namespace App\Models;

use App\Traits\Loadable;
use App\Traits\Reviewable;
use App\Traits\SetCurrency;
use Database\Factories\ShopFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Schema;

/**
 * App\Models\Shop
 *
 * @property int|null $id
 * @property string $uuid
 * @property string $slug
 * @property int $user_id
 * @property float $tax
 * @property float $rate_tax
 * @property float $percentage
 * @property string|null $phone
 * @property boolean $open
 * @property boolean $visibility
 * @property boolean $verify
 * @property string|null $background_img
 * @property int|null $delivery_type
 * @property string|null $logo_img
 * @property float $min_amount
 * @property string $status
 * @property array $lat_long
 * @property integer $age_limit
 * @property float $r_count
 * @property float $r_avg
 * @property float $r_sum
 * @property float $o_count
 * @property float $od_count
 * @property string|null $status_note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $type
 * @property array|null $delivery_time
 * @property-read Collection|Discount[] $discounts
 * @property-read int|null $discounts_count
 * @property-read Collection|Invitation[] $invitations
 * @property-read int|null $invitations_count
 * @property-read Collection|OrderDetail[] $orders
 * @property-read int|null $orders_count
 * @property-read Collection|Product[] $products
 * @property-read int|null $products_count
 * @property-read Collection|ShopPayment[] $shopPayments
 * @property-read int|null $shop_payments_count
 * @property-read Collection|Review[] $reviews
 * @property-read int|null $reviews_count
 * @property-read int|null $reviews_avg_rating
 * @property-read User|null $seller
 * @property-read ShopSubscription|null $subscription
 * @property-read Shop|null $parent
 * @property-read Collection|Shop[] $children
 * @property-read ShopTranslation|null $translation
 * @property-read Collection|ShopTranslation[] $translations
 * @property-read int|null $translations_count
 * @property-read Collection|User[] $users
 * @property-read int|null $users_count
 * @property-read int|null $locations_count
 * @property-read Collection|ShopWorkingDay[] $workingDays
 * @property-read int|null $working_days_count
 * @property-read Collection|ShopClosedDate[] $closedDates
 * @property-read int|null $closed_dates_count
 * @property-read Collection|ShopTag[] $tags
 * @property-read int|null $tags_count
 * @property-read Bonus|null $bonus
 * @property-read ShopDeliverymanSetting|null $shopDeliverymanSetting
 * @property-read Collection|ShopSocial[] $socials
 * @property-read ShopLocation|null $location
 * @property-read Collection|ShopLocation[] $locations
 * @method static ShopFactory factory(...$parameters)
 * @method static Builder|self filter($filter)
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereBackgroundImg($value)
 * @method static Builder|self whereCloseTime($value)
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder|self whereDeletedAt($value)
 * @method static Builder|self whereDeliveryRange($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereLogoImg($value)
 * @method static Builder|self whereMinAmount($value)
 * @method static Builder|self whereOpen($value)
 * @method static Builder|self whereOpenTime($value)
 * @method static Builder|self wherePercentage($value)
 * @method static Builder|self wherePhone($value)
 * @method static Builder|self whereShowType($value)
 * @method static Builder|self whereStatus($value)
 * @method static Builder|self whereStatusNote($value)
 * @method static Builder|self whereTax($value)
 * @method static Builder|self whereUpdatedAt($value)
 * @method static Builder|self whereUserId($value)
 * @method static Builder|self whereUuid($value)
 * @mixin Eloquent
 */
class Shop extends Model
{
    use HasFactory, Loadable, SetCurrency, Reviewable;

    protected $guarded = ['id'];

    protected $casts = [
        'lat_long'      => 'array',
        'delivery_time' => 'array',
        'open'          => 'bool',
        'visibility'    => 'bool',
        'verify'        => 'bool',
        'delivery_type' => 'int',
    ];

    const NEW       = 'new';
    const EDITED    = 'edited';
    const APPROVED  = 'approved';
    const REJECTED  = 'rejected';
    const INACTIVE  = 'inactive';

    const STATUS = [
        self::NEW       => self::NEW,
        self::EDITED    => self::EDITED,
        self::APPROVED  => self::APPROVED,
        self::REJECTED  => self::REJECTED,
        self::INACTIVE  => self::INACTIVE,
    ];

    const TYPE_SHOP = 1;

    const TYPES = [
        self::TYPE_SHOP => 'shop',
    ];

    const TYPES_BY = [
        'shop' => self::TYPE_SHOP,
    ];

    const DELIVERY_TYPE_IN_HOUSE = 1;
    const DELIVERY_TYPE_SELLER   = 2;

    const DELIVERY_TYPES = [
        self::DELIVERY_TYPE_IN_HOUSE => 'in_house',
        self::DELIVERY_TYPE_SELLER   => 'seller',
    ];

    const DELIVERY_TYPES_BY = [
        'in_house' => self::DELIVERY_TYPE_IN_HOUSE,
        'seller'   => self::DELIVERY_TYPE_SELLER,
    ];

    const DELIVERY_TIME_MINUTE  = 'minute';
    const DELIVERY_TIME_HOUR    = 'hour';
    const DELIVERY_TIME_DAY     = 'day';
    const DELIVERY_TIME_MONTH   = 'month';

    const DELIVERY_TIME_TYPE = [
        self::DELIVERY_TIME_MINUTE,
        self::DELIVERY_TIME_HOUR,
        self::DELIVERY_TIME_DAY,
        self::DELIVERY_TIME_MONTH,
    ];

    public function bonus(): HasOne
    {
        return $this->hasOne(Bonus::class)
            ->where('type', Bonus::TYPE_SUM)
            ->whereNull('stock_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ShopTag::class, 'assign_shop_tags', 'shop_id', 'shop_tag_id');
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ShopTranslation::class);
    }

    public function workingDays(): HasMany
    {
        return $this->hasMany(ShopWorkingDay::class);
    }

    public function closedDates(): HasMany
    {
        return $this->hasMany(ShopClosedDate::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(ShopTranslation::class);
    }

    public function shopDeliverymanSetting(): HasOne
    {
        return $this->hasOne(ShopDeliverymanSetting::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shopPayments(): HasMany
    {
        return $this->hasMany(ShopPayment::class);
    }

    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, Invitation::class,
            'shop_id', 'id', 'id', 'user_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'assignable');
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(ShopSubscription::class, 'shop_id')
            ->whereDate('expired_at', '>=', today())
            ->where([
                'active' => 1
            ])
            ->orderByDesc('id');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(ShopLocation::class);
    }

    public function location(): HasOne
    {
        return $this->hasOne(ShopLocation::class);
    }

    public function socials(): HasMany
    {
        return $this->hasMany(ShopSocial::class);
    }

    public function scopeFilter($query, array $filter)
    {
        $regionId   = data_get($filter, 'region_id');
        $countryId  = data_get($filter, 'country_id');
        $cityId     = data_get($filter, 'city_id');
        $areaId     = data_get($filter, 'area_id');
        $byLocation = $regionId || $countryId || $cityId || $areaId;
        $column     = data_get($filter, 'column', 'id');

        if (!Schema::hasColumn('shops', $column)) {
            $column = 'id';
        }

        $visibility = (int)Settings::where('key', 'by_subscription')->first()?->value;

        if ($visibility && request()->is('api/v1/rest/*')) {
            $filter['visibility'] = true;
        }

        $query
            ->when($byLocation, function ($query) use ($regionId, $countryId, $cityId, $areaId) {
                $query->whereHas('locations', function ($q) use ($regionId, $countryId, $cityId, $areaId) {
                    $q->where(function ($q) use ($regionId, $countryId, $cityId, $areaId) {
                        $q
                            ->when($regionId,  fn($q, $regionId)  => $q->where('region_id',  $regionId))
                            ->when($countryId, fn($q, $countryId) => $q->where('country_id', $countryId), $regionId  ? fn($q) => $q->whereNull('country_id') : fn($q) => $q)
                            ->when($cityId,    fn($q, $cityId)    => $q->where('city_id',    $cityId),    $countryId ? fn($q) => $q->whereNull('city_id') : fn($q) => $q)
                            ->when($areaId,    fn($q, $areaId)    => $q->where('area_id',    $areaId),    $cityId    ? fn($q) => $q->whereNull('area_id') : fn($q) => $q);
                    });
                });
            })
            ->when(data_get($filter, 'slug'), fn($q, $slug) => $q->where('slug', $slug))
            ->when(data_get($filter, 'user_id'), function ($q, $userId) {
                $q->where('user_id', $userId);
            })
            ->when(data_get($filter, 'status'), function ($q, $status) {
                $q->where('status', $status);
            })
            ->when(data_get($filter, 'type'), function ($q, $type) {
                $q->where('type', data_get(self::TYPES_BY, $type));
            })
            ->when(data_get($filter, 'delivery_type'), function ($q, $deliveryType) {
                $q->where('delivery_type', $deliveryType);
            })
            ->when(isset($filter['open']), function ($q) use($filter) {
                $q->where('open', $filter['open']);
            })
            ->when(isset($filter['visibility']), function ($q, $visibility) {
                $q->where('visibility', $visibility);
            })
            ->when(isset($filter['verify']), function ($q) use($filter) {
                $q->where('verify', $filter['verify']);
            })
            ->when(data_get($filter, 'bonus'), function (Builder $query) {
                $query->whereHas('bonus', function ($q) {
                    $q->where('expired_at', '>=', now());
                });
            })
            ->when(data_get($filter, 'deals'), function (Builder $query) {
                $query->where(function ($query) {
                    $query->whereHas('bonus', function ($q) {
                        $q->where('expired_at', '>=', now());
                    })->orWhereHas('discounts', function ($q) {
                        $q->where('end', '>=', now())->where('active', 1);
                    });
                });
            })
            ->when(data_get($filter, 'work_24_7'), function (Builder $query) {
                $query->whereHas('workingDays', fn($q) => $q->where('from', '01-00')->where('to', '>=', '23-00'));
            })
            ->when(data_get($filter, 'search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('id', $search)
                        ->orWhere('phone', 'LIKE', "%$search%")
                        ->orWhereHas('translations', function ($q) use ($search) {
                            $q->where('title', 'LIKE', "%$search%")
                                ->select('id', 'shop_id', 'locale', 'title');
                        });
                });
            })
            ->when(data_get($filter, 'take'), function (Builder $query, $take) {

                $query->whereHas('tags', function (Builder $q) use ($take) {
                    $q->when(is_array($take), fn($q) => $q->whereIn('id', $take), fn($q) => $q->where('id', $take));
                });

            })
            ->when(data_get($filter, 'fast_delivery'), function (Builder $q) {
                $q
                    ->where(function ($q) {
                        $q
                            ->where('delivery_time->type','minute')
                            ->orWhere('delivery_time->type','hour')
                            ->orWhere('delivery_time->type','day')
                            ->orWhere('delivery_time->type','month');
                    })
                    ->orderByRaw('CAST(JSON_EXTRACT(delivery_time, "$.from") AS from)', 'desc');
            })
            ->when(data_get($filter, 'order_by'), function (Builder $query, $orderBy) {

                switch ($orderBy) {
                    case 'new':
                        $query->orderBy('created_at', 'desc');
                        break;
                    case 'old':
                        $query->orderBy('created_at');
                        break;
                    case 'best_sale':
                        $query->orderBy('od_count', 'desc');
                        break;
                    case 'low_sale':
                        $query->orderBy('od_count');
                        break;
                    case 'high_rating':
                        $query->orderBy('r_avg', 'desc');
                        break;
                    case 'low_rating':
                        $query->orderBy('r_avg');
                        break;
                    case 'trust_you':
                        $ids = implode(', ', array_keys(Cache::get('shop-recommended-ids', [])));
                        if (!empty($ids)) {
                            $query->orderByRaw(DB::raw("FIELD(id, $ids) ASC"));
                        }
                        break;
                }

            }, fn($q) => $q->orderBy($column, data_get($filter, 'sort', 'desc')));
    }
}
