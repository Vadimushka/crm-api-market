<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer', 255);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('warehouse_id')->constrained();
            $table->enum('status', [
                OrderStatus::ACTIVE->value,
                OrderStatus::COMPLETED->value,
                OrderStatus::CANCELLED->value
            ])->default(OrderStatus::ACTIVE->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
