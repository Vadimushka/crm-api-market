<?php

use App\Enums\StockMovementType;
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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('warehouse_id')->constrained();
            $table->integer('amount'); // - расход, + приход
            $table->enum('operation_type', [StockMovementType::CREATED->value, StockMovementType::COMPLETED->value, StockMovementType::CANCELED->value]);
            $table->foreignId('operation_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('operation_id')->references('id')->on('orders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
