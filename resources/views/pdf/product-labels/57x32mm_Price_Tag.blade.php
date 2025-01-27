@extends('pdf.template')
@section('content')
    @php
        $users_warehouse_code = auth()->user()->warehouse_code;
        $products = collect($product_sku)->map(function($sku) {
            return \App\Models\Product::whereSku($sku)->with(['prices'])->first()->toArray();
        })->toArray();

    @endphp

    @if(empty($products))
        <div style="width: 100%; text-align: center; margin-top: 10mm;">
            <div class="product_name" style="height: 10mm; text-align: center;">Enter Product SKU</div>
        </div>
    @endif

    @foreach($products as $product)
        <div style="width: 100%; text-align: left">
            <div class="product_name">{{ $product['name'] }}</div>
            <div class="product_price"><span class="euroSymbol">&euro;</span>{{ $users_warehouse_code ? $product['prices'][$users_warehouse_code]['price'] : $product['price'] }}</div>
            <div class="product_sku">{{ $product['sku'] }}</div>
            <div class="product_barcode"><img src="data:image/svg,{{ DNS1D::getBarcodeSVG($product['sku'], 'C39', 0.55, 14, 'black', false) }}" alt="barcode"/></div>
        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    <style>
        @page {
            size: 57mm 32mm;
            margin: 1mm;
        }

        .product_name {
            font-size: 12pt;
            margin-top: 3px;
            margin-left: 5px;
            margin-right: 5px;
            text-align: left;
            word-wrap: anywhere;
        }

        .euroSymbol {
            margin-right: 10px;
            font-size: 20pt;
            text-align: right;
            font-family: sans-serif;
            font-weight: bold;
            word-wrap: anywhere;
        }

        .product_price {
            font-size: 24pt;
            margin-right: 5px;
            text-align: right;
            font-family: sans-serif;
            font-weight: bold;
            word-wrap: anywhere;
        }

        .product_sku {
            position: absolute;
            left: 5px;
            bottom: 18px;
            font-size: 8pt;
        }

        .product_barcode {
            position: absolute;
            left: 5px;
            bottom: 5px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>

@endsection
