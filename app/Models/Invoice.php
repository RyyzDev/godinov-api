<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'client_email',
        'client_phone',
        'invoice_date',
        'due_date',
        'invoice_items',
        'total_amount',
        'payment_status',
        'payment_method',     
        'amount_paid',        
        'payment_proof_url',  
        'notes',
    ];

    /**
     * Casting data agar otomatis berubah tipe saat diakses.
     */
    protected $casts = [
        'invoice_items' => 'array',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Boot function untuk menghitung total_amount secara otomatis
     * sesaat sebelum data disimpan (saving).
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($invoice) {
            if (is_array($invoice->invoice_items)) {
                $total = 0;
                foreach ($invoice->invoice_items as $item) {
                    // Logika: (rate * unit) - discount
                    $rate = $item['rate'] ?? 0;
                    $unit = $item['unit'] ?? 1;
                    $discount = $item['discount'] ?? 0;
                    
                    $total += ($rate * $unit) - $discount;
                }
                $invoice->total_amount = $total;
            }
        });
    }

    /**
     * Relasi ke Model Project.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function getRouteKeyName()
    {
        return 'project_id'; //param untuk get invoice berdasarkan project_id
    }
}