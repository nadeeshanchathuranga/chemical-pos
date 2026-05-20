<?php

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
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('batch_no')->nullable();
            
            $table->integer('stock_quantity')->default(0);
            $table->decimal('cost_price', 10, 2)->default(0);
            
            $table->decimal('retail_price', 10, 2)->default(0);
            $table->decimal('retail_discount', 5, 2)->default(0);
            $table->decimal('discounted_retail_price', 10, 2)->nullable();
            
            $table->decimal('wholesale_price', 10, 2)->default(0);
            $table->decimal('wholesale_discount', 5, 2)->default(0);
            $table->decimal('discounted_wholesale_price', 10, 2)->nullable();
            
            $table->date('expire_date')->nullable();
            $table->timestamps();
        });

        // Migrate existing product pricing and stock data to the new table
        $products = DB::table('products')->get();
        foreach ($products as $product) {
            DB::table('product_batches')->insert([
                'product_id' => $product->id,
                'batch_no' => $product->batch_no ?? null,
                'stock_quantity' => $product->stock_quantity ?? 0,
                'cost_price' => $product->cost_price ?? 0,
                'retail_price' => $product->retail_price ?? 0,
                'retail_discount' => $product->retail_discount ?? 0,
                'discounted_retail_price' => $product->discounted_retail_price,
                'wholesale_price' => $product->wholesale_price ?? 0,
                'wholesale_discount' => $product->wholesale_discount ?? 0,
                'discounted_wholesale_price' => $product->discounted_wholesale_price,
                'expire_date' => $product->expire_date ?? null,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};
