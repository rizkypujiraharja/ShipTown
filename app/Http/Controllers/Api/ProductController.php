<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductsRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::getSpatieQueryBuilder()
            ->simplePaginate(request()->input('per_page', 10));

        return ProductResource::collection($query);
    }

    public function store(StoreProductsRequest $request): JsonResponse
    {
        $product = Product::query()->updateOrCreate(
            ['sku' => $request->sku],
            $request->validated()
        );

        return response()->json($product, 200);
    }

    public function publish($sku)
    {
        $product = Product::query()->where('sku', $sku)->firstOrFail();

        $product->save();

        $this->respondOK200();
    }
}
