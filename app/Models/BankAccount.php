<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'bank_name',
        'account_number',
        'opening_balance',
        'current_balance',
        'note',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
    ];

    public function transactions()
    {
        return $this->hasMany(BankTransaction::class, 'bank_account_id', 'id')
                    ->orderBy('transaction_date', 'desc');
    }

    // Recompute balance from transactions + opening balance
    public function recalculateBalance(): void
    {
        $credits = $this->transactions()->where('type', 'Credit')->sum('amount');
        $debits  = $this->transactions()->where('type', 'Debit')->sum('amount');
        $this->current_balance = (float) $this->opening_balance + (float) $credits - (float) $debits;
        $this->save();
    }
}
