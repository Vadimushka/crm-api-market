<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\OrderUpdateRequest;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Stock;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Контроллер для управления заказами
 */
class OrderController extends Controller
{

    public function __construct(
        private StockService $stockService // Сервис работы с остатками
    )
    {

    }


    /**
     * Получить список заказов с фильтрацией и пагинацией
     *
     * @param Request $request
     * @return OrderCollection
     */
    public function index(Request $request): OrderCollection
    {
        // Получаем заказы с учетом фильтров
        $orders = Order::query()
            ->with(['warehouse', 'items.product'])
            ->filter($request->all())
            ->latest()
            ->paginate($request->get('per_page') ?? 15);

        return new OrderCollection($orders);
    }

    /**
     * Создать новый заказ
     *
     * @param OrderRequest $request
     * @return OrderResource|JsonResponse
     *
     * @throws \Throwable
     */
    public function store(OrderRequest $request): OrderResource|JsonResponse
    {
        try {
            DB::beginTransaction();

            $order = Order::create($request->only(['customer', 'warehouse_id']) + ['status' => OrderStatus::ACTIVE->value]);

            foreach ($request->get('items') as $item) {

                // Используем сервис для проверки остатков
                $this->stockService->checkStock(
                    $item['product_id'],
                    $request->warehouse_id,
                    $item['count']
                );

                $order->items()->create($item);

                // Записываем резервирование товара
                $this->stockService->recordMovement(
                    $item['product_id'],
                    $request->warehouse_id,
                    -$item['count'],
                    'order_created',
                    $order->id,
                    "Резервирование товара для заказа #{$order->id}"
                );
            }

            DB::commit();

            return new OrderResource($order->load('warehouse', 'items.product'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Просматриваем товар
     *
     * @param Order $order
     * @return OrderResource
     */
    public function show(Order $order)
    {
        return new OrderResource($order->load('warehouse', 'items.product'));
    }

    /**
     * Обновляем заказ
     *
     * @param OrderUpdateRequest $request
     * @param Order $order
     * @return OrderResource|JsonResponse
     * @throws \Throwable
     */
    public function update(OrderUpdateRequest $request, Order $order): OrderResource|JsonResponse
    {
        try {
            DB::beginTransaction();

            $order->update($request->only(['customer', 'warehouse_id']));

            if ($request->has('items')) {
                $order->items()->delete();

                foreach ($request->get('items') as $item) {
                    // Используем сервис для проверки остатков
                    $this->stockService->checkStock(
                        $item['product_id'],
                        $request->warehouse_id,
                        $item['count']
                    );

                    $order->items()->create($item);
                }
            }
            DB::commit();

            return new OrderResource($order->fresh()->load('warehouse', 'items.product'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Завершаем заказ
     *
     * @param Order $order
     * @return JsonResponse
     * @throws \Throwable
     */
    public function complete(Order $order): JsonResponse
    {
        if ($order->status !== OrderStatus::ACTIVE) {
            return response()->json(['message' => 'Заказ можно завершить только из активного статуса'], 422);
        }

        try {
            DB::beginTransaction();

            foreach ($order->items as $item) {
                // Используем сервис для списания товаров
                $this->stockService->decrementStock(
                    $item->product_id,
                    $order->warehouse_id,
                    $item->count
                );
            }

            $order->update([
                'status' => OrderStatus::COMPLETED,
                'completed_at' => now()
            ]);

            DB::commit();

            return response()->json(['message' => 'Заказ завершен']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Ошибка при завершении заказа'], 500);
        }
    }

    /**
     * Отменяем заказ
     *
     * @param Order $order
     * @return JsonResponse
     * @throws \Throwable
     */
    public function cancel(Order $order)
    {
        if (!in_array($order->status, [OrderStatus::ACTIVE, OrderStatus::COMPLETED])) {
            return response()->json(['message' => 'Заказ можно отменить только из активного или завершенного статуса'], 422);
        }

        try {
            DB::beginTransaction();

            if ($order->status === OrderStatus::COMPLETED) {
                foreach ($order->items as $item) {
                    // Используем сервис для возвращения товаров
                    $this->stockService->incrementStock(
                        $item->product_id,
                        $order->warehouse_id,
                        $item->count
                    );
                }
            }

            $order->update(['status' => OrderStatus::CANCELLED]);

            DB::commit();

            return response()->json(['message' => 'Заказ отменен']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Ошибка при отмене заказа'], 500);
        }
    }

    /**
     * Возобновляем заказ
     *
     * @param Order $order
     * @return JsonResponse
     * @throws \Throwable
     */
    public function resume(Order $order)
    {
        if ($order->status !== OrderStatus::CANCELLED) {
            return response()->json(['message' => 'Можно возобновить только отмененный заказ'], 422);
        }

        try {
            DB::beginTransaction();

            foreach ($order->items as $item) {
                $stock = Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $order->warehouse_id)
                    ->first();

                if (!$stock || $stock->stock < $item->count) {
                    throw new \Exception("Недостаточно товара {$item->product->name} на складе");
                }
            }

            $order->update(['status' => OrderStatus::ACTIVE]);

            DB::commit();

            return response()->json(['message' => 'Заказ возобновлен']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

}
