<?php

namespace App\Models;

use App\Traits\GeneratesUniqueCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Product extends Model
{
    use HasFactory, GeneratesUniqueCode;
    protected $fillable = [
        'category_id',
        'supplier_id',
        'name',
        'code',
        'size_id',
        'color_id',
        'cost_price',
        'retail_price',
        'retail_discount',
        'discounted_retail_price',
        'wholesale_price',
        'wholesale_discount',
        'discounted_wholesale_price',
        'stock_quantity',
        'barcode',
        'image',
        'expire_date',
        'batch_no',
        'total_quantity',
        'purchase_date',
        'is_promotion',
    ];

    // public static function boot()
    // {
    //     parent::boot();

    //     // Automatically generate a unique code when creating an order
    //     static::creating(function ($model) {
    //         $model->barcode = $model->generateUniqueCode(12);
    //     });
    // }

    /**
     * Get the effective selling price for a given sale type.
     *
     * If a discount exists and a discounted price is stored, use it.
     * Otherwise fall back to the base price for the requested type.
     *
     * @param  string  $type  'retail' or 'wholesale'
     * @return float
     */
    public function getFinalPrice(string $type = 'retail'): float
    {
        if ($type === 'wholesale') {
            if ($this->wholesale_discount > 0 && $this->discounted_wholesale_price > 0) {
                return (float) $this->discounted_wholesale_price;
            }
            return (float) $this->wholesale_price;
        }

        // Default: retail
        if ($this->retail_discount > 0 && $this->discounted_retail_price > 0) {
            return (float) $this->discounted_retail_price;
        }
        return (float) $this->retail_price;
    }

    /**
     * Get the base (non-discounted) price for a given sale type.
     *
     * @param  string  $type  'retail' or 'wholesale'
     * @return float
     */
    public function getBasePrice(string $type = 'retail'): float
    {
        return $type === 'wholesale'
            ? (float) $this->wholesale_price
            : (float) $this->retail_price;
    }

    /**
     * Get the discount percentage for a given sale type.
     *
     * @param  string  $type  'retail' or 'wholesale'
     * @return float
     */
    public function getDiscountPercent(string $type = 'retail'): float
    {
        return $type === 'wholesale'
            ? (float) ($this->wholesale_discount ?? 0)
            : (float) ($this->retail_discount ?? 0);
    }

    /**
     * Backward-compatible accessor: returns retail_price when
     * code still references $product->selling_price.
     *
     * @return float
     */
    public function getSellingPriceAttribute(): float
    {
        return (float) $this->retail_price;
    }

    /**
     * Backward-compatible accessor for discount → retail_discount.
     */
    public function getDiscountAttribute(): float
    {
        return (float) ($this->retail_discount ?? 0);
    }

    /**
     * Backward-compatible accessor for discounted_price → discounted_retail_price.
     */
    public function getDiscountedPriceAttribute(): ?float
    {
        return $this->discounted_retail_price !== null
            ? (float) $this->discounted_retail_price
            : null;
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id','id');
    }

    public function color()
    {
        return $this->belongsTo(Color::class, 'color_id','id');
    }


    public function size()
    {
        return $this->belongsTo(Size::class, 'size_id','id');
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id','id');
    }

    public function batches()
    {
        return $this->hasMany(ProductBatch::class, 'product_id');
    }

    protected $casts = [
        'expire_date' => 'date', // Cast expiry_date as a date
    ];

      public function promotionItems()
    {
        return $this->hasMany(PromotionItem::class, 'promotion_id');
    }

    public function components() // many Product via pivot with qty
    {
        return $this->belongsToMany(
            Product::class,
            'promotion_items',
            'promotion_id', // this product (pack)
            'product_id'    // component
        )->withPivot('quantity')->withTimestamps();
    }

    // packs that include THIS product as a component
    public function includedInPromotions()
    {
        return $this->belongsToMany(
            Product::class,
            'promotion_items',
            'product_id',    // this product as component
            'promotion_id'   // pack product
        )->withPivot('quantity');
    }


}
