<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_no',
        'stock_quantity',
        'cost_price',
        'retail_price',
        'retail_discount',
        'discounted_retail_price',
        'wholesale_price',
        'wholesale_discount',
        'discounted_wholesale_price',
        'expire_date',
    ];

    protected $casts = [
        'expire_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
