<!doctype html>
<html lang="en">
<?php
/** @var App\Models\Order[]|\Illuminate\Support\Collection $orders */
/** @var string $logo */
$products  = [];
$shops     = [];

foreach ($orders as $order) {

    $shops[] = [
        'total_tax' => $order->rate_total_tax,
        'title'     => $order->shop?->translation?->title,
        'tax'       => $order->shop?->tax,
    ];

    /** @var App\Models\Order $order */
    foreach($order->orderDetails as $orderDetail) {

        $title = "{$orderDetail->stock?->product?->translation?->title} (";

        foreach ($orderDetail->stock?->stockExtras?->sortDesc() as $item) {
            $title .= "{$item->group?->translation?->title}: {$item->value?->value}, ";
        }

        $title = rtrim($title, ', ');

        $products[] = [
            'id'                => $orderDetail->id,
            'title'             => "$title)",
            'rate_tax'          => $orderDetail->rate_tax,
            'quantity'          => $orderDetail->quantity,
            'rate_discount'     => $orderDetail->rate_discount,
            'rate_origin_price' => $orderDetail->rate_origin_price,
            'rate_total_price'  => $orderDetail->rate_total_price,
        ];
    }

}

$order = $orders[0];

$userName    = "{$order->user?->firstname} {$order->user?->lastname}";
$userPhone   = $order->phone ?? $order->user?->phone;
$address     = @$order->address['address'];
$position    = $order?->currency?->position;
$symbol      = $order?->currency?->symbol;
$deliveryFee = $orders->sum('delivery_fee') * $order->rate;
$serviceFee  = $orders->sum('service_fee') * $order->rate;
$totalPrice  = $orders->sum('total_price') * $order->rate - $deliveryFee - $serviceFee;
$totalTax    = $orders->sum('total_tax') * $order->rate;
$couponPrice = $orders->sum('coupon_price') * $order->rate;

?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, shrink-to-fit=no"
    >
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Order {{$order?->id}}</title>
    <link
          rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm"
          crossorigin="anonymous"
    >
    <style>
        html {
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
        }

        .logo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
        }

        .subtitle {
            margin-top: 50px;
        }

        .space {
            margin-top: 300px;
        }
    </style>
</head>
<body>
<div class="container d-flex justify-content-between">
    <div class="float-left">
        <img class="logo" src="{{$logo}}" alt="logo"/>
    </div>
    <div class="float-right">
        <h1 class="title">Invoice #{{ $order->id }}</h1>
        <h2 class="title gray">{{ $order->created_at?->format('Y-m-d') }}</h2>
    </div>
</div>
<div class="container d-flex justify-content-between" style="margin-top: 100px">
    <div class="float-left" style="margin-right: 50px">
        <h3 class="subtitle">Address place</h3>
        <div class="address__info">
            <div class="address__info--item">{!! $userName !!}</div>
            <div class="address__info--item">{!! $address !!}</div>
            <div class="address__info--item">{!! data_get($order, 'address.floor', '') .
                data_get($order, 'address.house', '') . data_get($order, 'address.office', '') !!}
            </div>
            <div class="address__info--item">
                {!! !empty($userPhone) ? '+' . str_replace('+', '', $userPhone) : '' !!}
            </div>
        </div>
    </div>
</div>
<div class="space"></div>
<table class="table table-striped mt-4 table-bordered">
    <thead>
    <tr>
        <th scope="col">#</th>
        <th scope="col">From</th>
        <th scope="col">Number</th>
        <th scope="col">Date</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <th scope="row">{{ $orders->implode('id', ',') }}</th>
        <td>{{ $order->created_at?->format('Y-m-d') }}</td>
        <td>{{ $orders->implode('id', ',') }}</td>
        <td>{{ $order->created_at?->format('Y-m-d') }}</td>
    </tr>
    </tbody>
</table>
<table class="table table-striped mt-4 table-bordered"> {{-- style="page-break-after: always;" --}}
    <thead>
    <tr>
        <th scope="col">#</th>
        <th scope="col">Product</th>
        <th scope="col">Total Tax</th>
        <th scope="col">Price without tax</th>
        <th scope="col">Quantity</th>
        <th scope="col">Discount</th>
        <th scope="col">Total price without tax</th>
    </tr>
    </thead>
    <tbody>
        @foreach($products as $product)
            <tr>
                <th scope="row">#{{ $product['id'] ?? 0 }}</th>
                <td>{{ $product['title'] ?? 'no name' }}</td>
                <td>{{ number_format(@($product['rate_tax'] * $product['quantity']) ?? 0, 2) }}</td>
                <td>
                    {{ $position === 'before' ? $symbol : '' }}
                    {{ number_format(($product['rate_origin_price'] ?? 0), 2) }}
                    {{ $position === 'after' ? $symbol : '' }}
                </td>
                <td>{{ $product['quantity'] ?? 0 }}</td>
                <td>{{ $product['rate_discount'] ?? 0 }}</td>
                <td>
                    {{ $position === 'before' ? $symbol : '' }}
                    {{ number_format((@($product['rate_total_price'] * $product['quantity']) ?? 0), 2) }}
                    {{ $position === 'after' ? $symbol : '' }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<table class="table table-striped mt-4 table-bordered">
    <thead>
    <tr>
        <th scope="col">Details</th>
        <th scope="col">Tax %</th>
        <th scope="col">{{ $position === 'before' ? $symbol : '' }} Tax {{ $position === 'after' ? $symbol : '' }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($shops as $shop)
        <tr>
            <th scope="row">{{$shop['title']}}</th>
            <td>{{ $shop['tax'] }}</td>
            <td>{{ $shop['total_tax'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<table class="table table-striped table-bordered">
    <thead>
    <tr>
        <th scope="col">Price</th>
        <th scope="col">Delivery fee</th>
        <th scope="col">Service fee</th>
        <th scope="col">Price</th>
        <th scope="col">Coupon</th>
        <th scope="col">
            {{ $position === 'before' ? $symbol : '' }} Total price {{ $position === 'after' ? $symbol : '' }}
        </th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <th scope="row">
            {{ $position === 'before' ? $symbol : '' }}
            {{ number_format(($totalPrice - $totalTax), 2) }}
            {{ $position === 'after' ? $symbol : '' }}
        </th>
        <td>
            {{ $position === 'before' ? $symbol : '' }}
            {{ number_format($deliveryFee, 2)  }}
            {{ $position === 'after' ? $symbol : '' }}
        </td>
        <td>
            {{ $position === 'before' ? $symbol : '' }}
            {{ number_format($serviceFee, 2) }}
            {{ $position === 'after' ? $symbol : '' }}
        </td>
        <td>
            {{ $position === 'before' ? $symbol : '' }}
            {{ number_format($totalPrice, 2) }}
            {{ $position === 'after' ? $symbol : '' }}
        </td>
        <td>
            {{ $position === 'before' ? $symbol : '' }}
            {{ number_format($couponPrice, 2) }}
            {{ $position === 'after' ? $symbol : '' }}
        </td>
        <td>
            {{ $position === 'before' ? $symbol : '' }}
            {{ number_format($totalPrice, 2) }}
            {{ $position === 'after' ? $symbol : '' }}
        </td>
    </tr>
    </tbody>
</table>
<div class="container float-left">
    <p><b>Delivery Date Time:</b> {{ $order->delivery_date }}</p>
</div>

<script
        src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
        crossorigin="anonymous">
</script>

<script
        src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
        crossorigin="anonymous">
</script>

<script
        src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
        crossorigin="anonymous">
</script>

</body>
</html>
