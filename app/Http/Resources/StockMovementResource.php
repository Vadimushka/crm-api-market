<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => [
                'id' => $this->product_id,
                'name' => $this->product->name,
            ],
            'warehouse' => [
                'id' => $this->warehouse_id,
                'name' => $this->warehouse->name,
            ],
            'amount' => $this->amount,
            'operation_type' => $this->operation_type,
            'operation_id' => $this->operation_id,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
