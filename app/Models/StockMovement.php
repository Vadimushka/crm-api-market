<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'amount',
        'operation_type',
        'operation_id',
        'description',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'operation_id');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['product_id'] ?? false, fn($query, $productId) => $query->where('product_id', $productId))
            ->when($filters['warehouse_id'] ?? false, fn($query, $warehouseId) => $query->where('warehouse_id', $warehouseId))
            ->when($filters['date_from'] ?? false, fn($query, $date) => $query->where('created_at', '>=', $date))
            ->when($filters['date_to'] ?? false, fn($query, $date) => $query->where('created_at', '<=', $date)
        );
    }

}
