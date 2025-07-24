<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Модель заказа
 *
 * @property int $id
 * @property string $customer
 * @property OrderStatus $status
 * @property int $warehouse_id
 * @property Carbon $created_at
 * @property Carbon|null $completed_at
 */
class Order extends Model
{

    /** @var array $fillable Поля, доступные для массового заполнения */
    protected $fillable = ['customer', 'warehouse_id', 'status', 'completed_at'];

    /** @var array $casts Преобразование типов атрибутов */
    protected $casts = [
        'status' => OrderStatus::class,
        'completed_at' => 'datetime',
    ];

    /**
     * Фильтрация заказов
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            // Фильтр по статусу
            ->when($filters['status'] ?? null, fn($query, $status) => $query->where('status', $status))
            // Фильтр по складу
            ->when($filters['warehouse_id'] ?? null, fn($query, $warehouseId) => $query->where('warehouse_id', $warehouseId))
            // Фильтр по дате от
            ->when($filters['date_from'] ?? null, fn($query, $date) => $query->where('created_at', '=>', $date))
            // Фильтр по дате до
            ->when($filters['date_to'] ?? null, fn($query, $date) => $query->where('created_at', '<=', $date))
            // Поиск по клиенту
            ->when($filters['search'] ?? null, fn($query, $search) => $query->where('customer', 'like', '%' . $search . '%'));
    }

    /**
     * Связь с элементами заказа
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Связь со складом
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }



}
