<?php
declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\OrderCoupon
 *
 * @property int $id
 * @property int $order_id
 * @property int $user_id
 * @property string $name
 * @property float|null $price
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereName($value)
 * @method static Builder|self whereOrderId($value)
 * @method static Builder|self wherePrice($value)
 * @method static Builder|self whereUserId($value)
 * @mixin Eloquent
 */
class OrderCoupon extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public $timestamps = false;

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
