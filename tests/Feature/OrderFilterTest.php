<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Warehouse;
use function Pest\Laravel\getJson;

it('filters orders by status', function () {
    $warehouse = Warehouse::factory()->create();
    Order::factory()->create(['status' => OrderStatus::ACTIVE, 'warehouse_id' => $warehouse->id]);
    Order::factory()->create(['status' => OrderStatus::COMPLETED, 'warehouse_id' => $warehouse->id]);

    $response = getJson('/api/orders?status=completed');

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.status', 'completed');
});

it('filters orders by date range', function () {
    $warehouse = Warehouse::factory()->create();
    Order::factory()->create([
        'warehouse_id' => $warehouse->id,
        'created_at' => '2023-01-10 00:00:00' // Вне диапазона
    ]);

    $targetOrder = Order::factory()->create([
        'warehouse_id' => $warehouse->id,
        'created_at' => '2023-02-01 00:00:00' // В диапазоне
    ]);

    Order::factory()->create([
        'warehouse_id' => $warehouse->id,
        'created_at' => '2023-03-01 00:00:00' // Вне диапазона
    ]);

    $response = getJson('/api/orders?date_from=2023-01-15&date_to=2023-02-15');

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $targetOrder->id);
});
