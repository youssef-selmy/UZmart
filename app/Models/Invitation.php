<?php
declare(strict_types=1);

namespace App\Models;

use Database\Factories\InvitationFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Invitation
 *
 * @property int $id
 * @property int $shop_id
 * @property int $user_id
 * @property string|null $role
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Shop $shop
 * @property-read User $user
 * @method static InvitationFactory factory(...$parameters)
 * @method static Builder|self filter($filter)
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereRole($value)
 * @method static Builder|self whereShopId($value)
 * @method static Builder|self whereStatus($value)
 * @method static Builder|self whereUpdatedAt($value)
 * @method static Builder|self whereUserId($value)
 * @mixin Eloquent
 */
class Invitation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    const STATUS = [
        'new'       => 1,
        'viewed'    => 2,
        'excepted'  => 3,
        'rejected'  => 4
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getStatusKey($value)
    {
        foreach (self::STATUS as $index => $status) {
            if ($value == $status) {
                return $index;
            }
        }
    }

    public function scopeFilter($query, array $filter)
    {
        $query
            ->when(data_get($filter, 'user_id'), function ($q, $userId) {
                $q->where('user_id', $userId);
            })
            ->when(data_get($filter, 'shop_id'), function ($q, $shopId) {
                $q->where('shop_id', $shopId);
            });
    }
}
