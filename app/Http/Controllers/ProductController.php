<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;

class ProductController extends Controller
{

    public function index(): ProductCollection
    {
        return new ProductCollection(Product::with('warehouses')->get());
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load('warehouses'));
    }

}
