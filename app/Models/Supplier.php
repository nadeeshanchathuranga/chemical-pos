<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Supplier extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'contact',
        'email',
        'address',
        'image',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'supplier_id', 'id');
    }

    public function supplierPayments()
    {
        return $this->hasMany(SupplierPayment::class, 'supplier_id', 'id');
    }
}
