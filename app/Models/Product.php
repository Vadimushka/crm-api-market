<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * Модель товара
 *
 * @property int $id
 * @property string $name
 * @property float $price
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Product extends Model
{

    use HasFactory;

    /** @var array $fillable Поля, доступные для массового заполнения */
    protected $fillable = ['name', 'price'];

    /**
     * Связь с элементами заказов
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Связь со складами через остатки
     */
    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'stocks')->withPivot('stock');
    }

}
