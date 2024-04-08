<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Traits\Loggable;
use Illuminate\Database\Seeder;
use Throwable;

class PaymentSeeder extends Seeder
{
    use Loggable;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $payments = [
            ['tag' => 'cash',         'input' => 1],  //input sort in ui
            ['tag' => 'wallet',       'input' => 2],  //input sort in ui
            ['tag' => 'zain-cash',    'input' => 3],  //input sort in ui
            ['tag' => 'paytabs',      'input' => 4],  //input sort in ui
            ['tag' => 'flw',          'input' => 5],  //input sort in ui
            ['tag' => 'paystack',     'input' => 6],  //input sort in ui
            ['tag' => 'mercado-pago', 'input' => 7],  //input sort in ui
            ['tag' => 'razorpay',     'input' => 8],  //input sort in ui
            ['tag' => 'stripe',       'input' => 9],  //input sort in ui
            ['tag' => 'paypal',       'input' => 10],  //input sort in ui
            ['tag' => 'moya-sar',     'input' => 11], //input sort in ui
            ['tag' => 'mollie',       'input' => 12], //input sort in ui
        ];

        foreach ($payments as $payment) {
            try {
                Payment::updateOrCreate([
                    'tag'   => data_get($payment, 'tag')
                ], [
                    'input' => data_get($payment, 'input')
                ]);
            } catch (Throwable $e) {
                $this->error($e);
            }
        }

    }

}
