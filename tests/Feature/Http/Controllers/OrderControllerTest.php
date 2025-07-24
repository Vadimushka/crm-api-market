<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;

it('creates order with items', function () {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create(['price' => 100]);
    Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'stock' => 10
    ]);

    $response = postJson('/api/orders', [
        'customer' => 'John Doe',
        'warehouse_id' => $warehouse->id,
        'items' => [
            [
                'product_id' => $product->id,
                'count' => 2
            ]
        ]
    ]);

    $response
        ->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'id',
                'customer',
                'items' => [
                    ['id', 'count', 'product']
                ],
                'total'
            ]
        ])
        ->assertJsonPath('data.total', 200);
});

it('fails when stock is insufficient', function () {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();
    Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'stock' => 1
    ]);

    $response = postJson('/api/orders', [
        'customer' => 'John Doe',
        'warehouse_id' => $warehouse->id,
        'items' => [
            [
                'product_id' => $product->id,
                'count' => 2
            ]
        ]
    ]);

    $response->assertStatus(422);
});

it('completes order and decrements stock', function () {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();
    $order = Order::factory()->create([
        'warehouse_id' => $warehouse->id,
        'status' => OrderStatus::ACTIVE
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'count' => 3
    ]);
    Stock::factory()->create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'stock' => 10
    ]);

    $response = patchJson("/api/orders/{$order->id}/complete");

    $response->assertStatus(200);
    expect($order->fresh()->status)->toBe(OrderStatus::COMPLETED);
    assertDatabaseHas('stocks', [
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'stock' => 7
    ]);
});
