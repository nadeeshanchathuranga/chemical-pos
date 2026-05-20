<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'retail_price')) {
                $table->decimal('retail_price', 10, 2)->default(0)->after('cost_price');
            }
            if (!Schema::hasColumn('products', 'retail_discount')) {
                $table->decimal('retail_discount', 5, 2)->default(0)->after('retail_price');
            }
            if (!Schema::hasColumn('products', 'discounted_retail_price')) {
                $table->decimal('discounted_retail_price', 10, 2)->nullable()->after('retail_discount');
            }
            if (!Schema::hasColumn('products', 'wholesale_price')) {
                $table->decimal('wholesale_price', 10, 2)->default(0)->after('discounted_retail_price');
            }
            if (!Schema::hasColumn('products', 'wholesale_discount')) {
                $table->decimal('wholesale_discount', 5, 2)->default(0)->after('wholesale_price');
            }
            if (!Schema::hasColumn('products', 'discounted_wholesale_price')) {
                $table->decimal('discounted_wholesale_price', 10, 2)->nullable()->after('wholesale_discount');
            }
        });

        if (Schema::hasColumn('products', 'selling_price')) {
            DB::statement('UPDATE products SET retail_price = selling_price WHERE retail_price = 0');
        }
        if (Schema::hasColumn('products', 'discount')) {
            DB::statement('UPDATE products SET retail_discount = discount WHERE retail_discount = 0');
        }
        if (Schema::hasColumn('products', 'discounted_price')) {
            DB::statement('UPDATE products SET discounted_retail_price = discounted_price WHERE discounted_retail_price IS NULL');
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'retail_price')) {
                $table->dropColumn('retail_price');
            }
            if (Schema::hasColumn('products', 'retail_discount')) {
                $table->dropColumn('retail_discount');
            }
            if (Schema::hasColumn('products', 'discounted_retail_price')) {
                $table->dropColumn('discounted_retail_price');
            }
            if (Schema::hasColumn('products', 'wholesale_price')) {
                $table->dropColumn('wholesale_price');
            }
            if (Schema::hasColumn('products', 'wholesale_discount')) {
                $table->dropColumn('wholesale_discount');
            }
            if (Schema::hasColumn('products', 'discounted_wholesale_price')) {
                $table->dropColumn('discounted_wholesale_price');
            }
        });
    }
};
