<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Payment;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'employee_id',
        'user_id',
        'order_id',
        'total_amount',
        'discount',
        'payment_method',
        'sale_date',
        'total_cost',
        'cash',
        'custom_discount',
        'custom_discount_type',
        'status',
        'is_credit',
        'paid_amount',
        'balance_due',
        'closing_date',
    ];




    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id','id');
    }

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class, 'order_id','id');
    }
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id','id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id','id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'sale_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }
}
