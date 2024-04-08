<?php
declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\PushNotification
 *
 * @property int $id
 * @property int $model_id
 * @property string $model_type
 * @property string $type
 * @property string $title
 * @property string $body
 * @property array $data
 * @property int $user_id
 * @property User $user
 * @property User|Order|Blog|null $model
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $read_at
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereUpdatedAt($value)
 * @mixin Eloquent
 */
class PushNotification extends Model
{
    protected $guarded = ['id'];
    protected $casts   = [
        'data' => 'array',
    ];

    const NEW_ORDER             = 'new_order';
    const NEW_PARCEL_ORDER      = 'new_parcel_order';
    const NEW_USER_BY_REFERRAL  = 'new_user_by_referral';
    const STATUS_CHANGED        = 'status_changed';
    const NEWS_PUBLISH          = 'news_publish';
    const ADD_CASHBACK          = 'add_cashback';
    const WALLET_TOP_UP         = 'wallet_top_up';
    const WALLET_WITHDRAW       = 'wallet_withdraw';
    const SHOP_APPROVED         = 'shop_approved';

    const TYPES = [
        self::NEW_ORDER             => self::NEW_ORDER,
        self::NEW_PARCEL_ORDER      => self::NEW_PARCEL_ORDER,
        self::NEW_USER_BY_REFERRAL  => self::NEW_USER_BY_REFERRAL,
        self::STATUS_CHANGED        => self::STATUS_CHANGED,
        self::NEWS_PUBLISH          => self::NEWS_PUBLISH,
        self::ADD_CASHBACK          => self::ADD_CASHBACK,
        self::WALLET_TOP_UP         => self::WALLET_TOP_UP,
        self::WALLET_WITHDRAW       => self::WALLET_WITHDRAW,
        self::SHOP_APPROVED         => self::ADD_CASHBACK,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }
}
