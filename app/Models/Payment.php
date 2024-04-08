<?php
declare(strict_types=1);

namespace App\Models;

use Database\Factories\PaymentFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * App\Models\Payment
 *
 * @property int $id
 * @property string|null $tag
 * @property int $input
 * @property int $sandbox
 * @property boolean $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ShopPayment|null $shopPayment
 * @property-read  PaymentPayload|HasOne|null $paymentPayload
 * @method static PaymentFactory factory(...$parameters)
 * @method static Builder|self newModelQuery()
 * @method static Builder|self newQuery()
 * @method static Builder|self query()
 * @method static Builder|self whereActive($value)
 * @method static Builder|self whereCreatedAt($value)
 * @method static Builder|self whereId($value)
 * @method static Builder|self whereInput($value)
 * @method static Builder|self whereSandbox($value)
 * @method static Builder|self whereTag($value)
 * @method static Builder|self whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Payment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'sandbox' => 'bool',
        'active'  => 'bool',
    ];

    public function shopPayment(): BelongsTo
    {
        return $this->belongsTo(ShopPayment::class,'id','payment_id');
    }

    public function paymentPayload(): HasOne
    {
        return $this->hasOne(PaymentPayload::class);
    }
}
