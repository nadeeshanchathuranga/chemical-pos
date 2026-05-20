<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cheque extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'cheque_number',
        'bank_name',
        'branch',
        'amount',
        'cheque_date',
        'due_date',
        'payee_payer',
        'status',
        'bank_account_id',
        'bank_transaction_id',
        'supplier_id',
        'note',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'cheque_date' => 'date',
        'due_date'    => 'date',
    ];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }
}
