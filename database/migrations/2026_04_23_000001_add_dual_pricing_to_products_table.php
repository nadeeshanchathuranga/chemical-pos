<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Converts the single selling_price / discount / discounted_price columns
     * into separate retail and wholesale pricing columns.
     *
     * Data preservation:
     *  - selling_price       → retail_price
     *  - discount            → retail_discount
     *  - discounted_price    → discounted_retail_price
     *  - wholesale columns default to 0 / null (to be filled by the user)
     */
    public function up(): void
    {
        // 1. Add the new dual-pricing columns if they do not already exist
        if (! Schema::hasColumn('products', 'retail_price')
            || ! Schema::hasColumn('products', 'retail_discount')
            || ! Schema::hasColumn('products', 'discounted_retail_price')
            || ! Schema::hasColumn('products', 'wholesale_price')
            || ! Schema::hasColumn('products', 'wholesale_discount')
            || ! Schema::hasColumn('products', 'discounted_wholesale_price')) {
            Schema::table('products', function (Blueprint $table) {
                if (! Schema::hasColumn('products', 'retail_price')) {
                    $table->decimal('retail_price', 10, 2)->default(0)->after('cost_price');
                }
                if (! Schema::hasColumn('products', 'retail_discount')) {
                    $table->decimal('retail_discount', 5, 2)->default(0)->after('retail_price');
                }
                if (! Schema::hasColumn('products', 'discounted_retail_price')) {
                    $table->decimal('discounted_retail_price', 10, 2)->nullable()->after('retail_discount');
                }
                if (! Schema::hasColumn('products', 'wholesale_price')) {
                    $table->decimal('wholesale_price', 10, 2)->default(0)->after('discounted_retail_price');
                }
                if (! Schema::hasColumn('products', 'wholesale_discount')) {
                    $table->decimal('wholesale_discount', 5, 2)->default(0)->after('wholesale_price');
                }
                if (! Schema::hasColumn('products', 'discounted_wholesale_price')) {
                    $table->decimal('discounted_wholesale_price', 10, 2)->nullable()->after('wholesale_discount');
                }
            });
        }

        // 2. Copy existing data into the new retail columns
        DB::statement('UPDATE products SET retail_price = selling_price');
        DB::statement('UPDATE products SET retail_discount = discount');
        DB::statement('UPDATE products SET discounted_retail_price = discounted_price');

        // 3. Drop the old columns if they still exist
        foreach (['selling_price', 'discount', 'discounted_price'] as $column) {
            if (Schema::hasColumn('products', $column)) {
                Schema::table('products', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Re-add the old columns if they do not exist
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'selling_price')) {
                $table->decimal('selling_price', 8, 2)->default(0)->after('cost_price');
            }
            if (! Schema::hasColumn('products', 'discount')) {
                $table->decimal('discount', 5, 2)->default(0)->after('selling_price');
            }
            if (! Schema::hasColumn('products', 'discounted_price')) {
                $table->decimal('discounted_price', 10, 2)->nullable()->after('discount');
            }
        });

        // 2. Copy retail data back
        DB::statement('UPDATE products SET selling_price = retail_price');
        DB::statement('UPDATE products SET discount = retail_discount');
        DB::statement('UPDATE products SET discounted_price = discounted_retail_price');

        // 3. Drop the new columns if they exist
        $newColumns = [
            'retail_price',
            'retail_discount',
            'discounted_retail_price',
            'wholesale_price',
            'wholesale_discount',
            'discounted_wholesale_price',
        ];

        foreach ($newColumns as $column) {
            if (Schema::hasColumn('products', $column)) {
                Schema::table('products', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
