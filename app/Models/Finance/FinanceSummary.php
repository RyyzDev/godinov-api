<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceSummary extends Model
{
    use HasFactory;

    protected $table = 'finance_summaries';

    protected $fillable = [
        'total_revenue',
        'total_receivables',
        'total_partially_paid_revenue',
        'total_expenses',
        'net_cash_flow',
    ];

    protected $casts = [
        'total_revenue' => 'decimal:2',
        'total_receivables' => 'decimal:2',
        'total_partially_paid_revenue' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'net_cash_flow' => 'decimal:2',
    ];
}
