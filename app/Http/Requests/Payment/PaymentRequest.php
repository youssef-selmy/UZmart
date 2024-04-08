<?php
declare(strict_types=1);

namespace App\Http\Requests\Payment;

use App\Http\Requests\BaseRequest;
use App\Http\Requests\Order\StoreRequest;
use App\Http\Requests\ParcelOrder\StoreRequest as ParcelOrderStoreRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $userId         = auth('sanctum')->id();

        $cartId         = request('cart_id');
        $parcelId       = request('parcel_id');
        $subscriptionId = request('subscription_id');
        $adsPackageId   = request('ads_package_id');
        $walletId       = request('wallet_id');

        $rules = [];

        if ($cartId) {
            $rules = (new StoreRequest)->rules();
        }
        //else if ($parcelId) {
        //            $rules = (new ParcelOrderStoreRequest)->rules();
        //        }

        return [
            'cart_id' => [
                !$adsPackageId && !$parcelId && !$subscriptionId && !$walletId ? 'required' : 'nullable',
                Rule::exists('carts', 'id')->where('owner_id', $userId)
            ],
            'parcel_id' => [
                !$cartId && !$adsPackageId && !$subscriptionId && !$walletId ? 'required' : 'nullable',
                Rule::exists('parcel_orders', 'id')->where('user_id', $userId)
            ],
            'subscription_id' => [
                !$cartId && !$adsPackageId && !$parcelId && !$walletId ? 'required' : 'nullable',
                Rule::exists('subscriptions', 'id')->where('active', true)
            ],
            'ads_package_id' => [
                !$cartId && !$parcelId && !$subscriptionId && !$walletId ? 'required' : 'nullable',
                Rule::exists('ads_packages', 'id')
                    ->where('active', true)
            ],
            'wallet_id' => [
                !$cartId && !$adsPackageId && !$parcelId && !$subscriptionId ? 'required' : 'nullable',
                Rule::exists('wallets', 'id')->where('user_id', auth('sanctum')->id())
            ],
            'total_price' => [
                !$cartId && !$adsPackageId && !$parcelId && !$subscriptionId ? 'required' : 'nullable',
                'numeric'
            ],
        ] + $rules;
    }

}
