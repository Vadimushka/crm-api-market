<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Здесь регистрируются API-маршруты приложения. Эти маршруты
| загружаются группой RouteServiceProvider.
|
*/

// Маршруты для складов
Route::apiResource('warehouses', WarehouseController::class)->only(['index', 'show']);

// Маршруты для товаров
Route::apiResource('products', ProductController::class)->only(['index', 'show']);

// Группа маршрутов для заказов
Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']); // Получить список заказов
    Route::post('/', [OrderController::class, 'store']); // Создать заказ
    Route::get('/{order}', [OrderController::class, 'show']); // Просмотреть заказ
    Route::put('/{order}', [OrderController::class, 'update']); // Обновить заказ
    Route::patch('/{order}/complete', [OrderController::class, 'complete']); // Завершить заказ
    Route::patch('/{order}/cancel', [OrderController::class, 'cancel']); // Отменить заказ
    Route::patch('/{order}/resume', [OrderController::class, 'resume']); // Возобновить заказ
});
