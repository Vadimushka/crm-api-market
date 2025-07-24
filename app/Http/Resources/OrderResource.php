<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Ресурс для преобразования заказа в JSON
 *
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer' => $this->customer,
            'status' => $this->status->value,
            'created_at' => $this->created_at->format('Y-m-d H:i'),
            'completed_at' => $this->completed_at?->format('Y-m-d H:i'),
            'warehouse' => [
                'id' => $this->warehouse->id,
                'name' => $this->warehouse->name,
            ],
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'price' => $item->product->price,
                    ],
                    'count' => $item->count,
                    'total' => $item->count * $item->product->price,
                ];
            }),
            'total' => $this->items->sum(fn($item) => $item->count * $item->product->price),
        ];
    }
}
