<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Command\Command as CommandAlias;

class SeedTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-test-data
                            {--products=50 : Number of products to create}
                            {--warehouses=5 : Number of warehouses to create}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed database with test data for products, warehouses and stocks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to seed test data...');

        DB::transaction(function () {
            $this->createWarehouses();
            $this->createProducts();
            $this->createStocks();
        });

        $this->info('Test data seeded successfully!');

        return CommandAlias::SUCCESS;
    }

    protected function createWarehouses(): void
    {
        $warehouseCount = (int)$this->option('warehouses');

        $this->info("Creating {$warehouseCount} warehouses...");

        Warehouse::factory()
            ->count($warehouseCount)
            ->create();
    }


    protected function createProducts(): void
    {
        $productCount = (int)$this->option('products');

        $this->info("Creating {$productCount} products...");

        Product::factory()
            ->count($productCount)
            ->create();
    }

    protected function createStocks(): void
    {
        $this->info('Creating stocks for all products in all warehouses...');

        $warehouses = Warehouse::all();
        $products = Product::all();

        $progressBar = $this->output->createProgressBar($warehouses->count() * $products->count());
        $progressBar->start();

        foreach ($warehouses as $warehouse) {
            foreach ($products as $product) {
                Stock::create([
                    'warehouse_id' => $warehouse->id,
                    'product_id' => $product->id,
                    'stock' => rand(0, 100), // Random stock between 0 and 100
                ]);

                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine();
    }
}
