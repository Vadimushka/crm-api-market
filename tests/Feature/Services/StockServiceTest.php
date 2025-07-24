<?php

namespace Tests\Feature\Services;

use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Services\StockService;
use Exception;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->service = app(StockService::class);
});

it('successfully checks available stock', function () {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();
    Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'stock' => 10
    ]);

    $this->service->checkStock($product->id, $warehouse->id, 5);
})->throwsNoExceptions();

it('throws exception when stock is insufficient', function () {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();
    Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'stock' => 3
    ]);

    $this->expectException(Exception::class);
    $this->expectExceptionMessageMatches("/Недостаточно товара {$product->name} на складе. Доступно: 3, требуется: 5/");

    $this->service->checkStock($product->id, $warehouse->id, 5);
});

it('correctly decrements stock', function () {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();
    Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'stock' => 10
    ]);

    $this->service->decrementStock($product->id, $warehouse->id, 3);

    assertDatabaseHas('stocks', [
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'stock' => 7
    ]);
});
