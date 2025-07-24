<?php

namespace App\Http\Controllers;

use App\Http\Resources\StockMovementCollection;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{

    public function index(Request $request): StockMovementCollection
    {
        $movements = StockMovement::with(['product', 'warehouse', 'order'])
            ->filter($request->all())
            ->latest()
            ->paginate($request->query('per_page', 15));

        return new StockMovementCollection($movements);
    }

}
