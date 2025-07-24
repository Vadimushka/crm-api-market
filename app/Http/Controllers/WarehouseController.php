<?php

namespace App\Http\Controllers;

use App\Http\Resources\WarehouseCollection;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;

class WarehouseController extends Controller
{

    public function index(): WarehouseCollection
    {
        return new WarehouseCollection(Warehouse::all());
    }

    public function show(Warehouse $warehouse): WarehouseResource
    {
        return new WarehouseResource($warehouse->load('products'));
    }


}
