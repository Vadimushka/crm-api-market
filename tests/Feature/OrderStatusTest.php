<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Warehouse;
use function Pest\Laravel\patchJson;

it('cancels active order', function () {
    $warehouse = Warehouse::factory()->create();
    $order = Order::factory()->create(['status' => OrderStatus::ACTIVE, 'warehouse_id' => $warehouse->id]);

    patchJson("/api/orders/{$order->id}/cancel")
        ->assertOk();

    expect($order->fresh()->status)->toBe(OrderStatus::CANCELLED);
});

it('fails to cancel non-active order', function () {
    $warehouse = Warehouse::factory()->create();
    $order = Order::factory()->create(['status' => OrderStatus::CANCELLED, 'warehouse_id' => $warehouse->id]);

    patchJson("/api/orders/{$order->id}/cancel")
        ->assertStatus(422);
});

it('resumes cancelled order', function () {
    $warehouse = Warehouse::factory()->create();
    $order = Order::factory()->create(['status' => OrderStatus::CANCELLED, 'warehouse_id' => $warehouse->id]);

    patchJson("/api/orders/{$order->id}/resume")
        ->assertOk();

    expect($order->fresh()->status)->toBe(OrderStatus::ACTIVE);
});
