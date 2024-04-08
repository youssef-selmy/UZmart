<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use App\Models\Payment;
use App\Models\PaymentPayload;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\PaymentService\StripeService;
use Http;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Redirect;

class StripeController extends PaymentBaseController
{
    public function __construct(private StripeService $service)
    {
        parent::__construct($service);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function resultTransaction(Request $request): RedirectResponse
    {
        $parcelId       = (int)$request->input('parcel_id');
        $adsPackageId   = (int)$request->input('ads_package_id');
        $subscriptionId = (int)$request->input('subscription_id');
        $walletId       = (int)$request->input('wallet_id');
        csrf_token();
        $to = config('app.front_url');

        if ($parcelId) {
            $to = config('app.front_url') . "parcels/$parcelId";
        } else if ($adsPackageId) {
            $to = config('app.admin_url') . "seller/shop-ads/$adsPackageId";
        } else if ($subscriptionId) {
            $to = config('app.admin_url') . "seller/subscriptions/$subscriptionId";
        } else if ($walletId) {

            /** @var Wallet $wallet */
            $wallet = Wallet::with('user.roles')->find($walletId);

            $to = config('app.front_url') . "wallet";

            if ($wallet?->user?->hasRole(['seller', 'admin', 'moderator', 'deliveryman', 'manager'])) {
                $to = config('app.admin_url');
            }

        }

        return Redirect::to($to);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function paymentWebHook(Request $request): void
    {
        $token = $request->input('data.object.id');

        $payment = Payment::where('tag', 'stripe')->first();

        $paymentPayload = PaymentPayload::where('payment_id', $payment?->id)->first();
        $payload        = $paymentPayload?->payload;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . data_get($payload, 'stripe_sk')
        ])
            ->get("https://api.stripe.com/v1/checkout/sessions?limit=1&payment_intent=$token")
            ->json();

        $token = data_get($response, 'data.0.id');

        $status = match (data_get($response, 'data.0.payment_status')) {
            'succeeded', 'paid'			 => Transaction::STATUS_PAID,
            'payment_failed', 'canceled' => Transaction::STATUS_CANCELED,
            default				  		 => 'progress',
        };

        $this->service->afterHook($token, $status, $request->input('data.object.id'));
    }

}
