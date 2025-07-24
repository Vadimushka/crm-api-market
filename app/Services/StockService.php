<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\DB;

class StockService
{

    /**
     * Проверить наличие товара на складе
     *
     * @param int $productId ID товара
     * @param int $warehouseId ID склада
     * @param int $requiredCount Требуемое количество
     * @return void
     * @throws Exception
     */
    public function checkStock(int $productId, int $warehouseId, int $requiredCount): void
    {
        $stock = Stock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if (!$stock) {
            $product = Product::findOrFail($productId);
            throw new Exception("Товар {$product->name} отсутствует на складе");
        }

        if ($stock->stock < $requiredCount) {
            $product = Product::findOrFail($productId);
            throw new Exception("Недостаточно товара {$product->name} на складе. Доступно: {$stock->stock}, требуется: {$requiredCount}");
        }
    }

    /**
     * Уменьшить остаток товара
     *
     * @param int $productId ID товара
     * @param int $warehouseId ID склада
     * @param int $count Количество для списания
     * @return void
     */
    public function decrementStock(int $productId, int $warehouseId, int $count): void
    {
        DB::transaction(function () use ($productId, $warehouseId, $count) {
            Stock::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->decrement('stock', $count);

            $this->recordMovement($productId, $warehouseId, -$count, 'order_completed');
        });
    }

    /**
     * Увеличить остаток товара
     *
     * @param int $productId ID товара
     * @param int $warehouseId ID склада
     * @param int $count Количество для возврата
     * @return void
     */
    public function incrementStock(int $productId, int $warehouseId, int $count): void
    {
        DB::transaction(function () use ($productId, $warehouseId, $count) {
            Stock::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->increment('stock', $count);

            $this->recordMovement($productId, $warehouseId, $count, 'order_canceled');
        });
    }

    public function recordMovement($productId, $warehouseId, $amount, $operationType, $operationId = null, $description = null): void
    {
        StockMovement::create([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'amount' => $amount,
            'operation_type' => $operationType,
            'operation_id' => $operationId,
            'description' => $description,
        ]);
    }

}
