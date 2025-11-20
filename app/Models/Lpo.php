<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lpo extends Model
{
    use HasFactory;

    protected $fillable = [
        'lpo_number',
        'requisition_id',
        'supplier_id',
        'prepared_by',
        'issued_by',
        'issued_at',
        'status',
        'subtotal',
        'tax',
        'vat_amount',
        'other_charges',
        'total',
        'delivery_date',
        'terms',
        'notes'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'total' => 'decimal:2',
        'issued_at' => 'datetime',
        'delivery_date' => 'datetime',
    ];

      // Add VAT calculation method
    public function calculateVatAmount()
    {
        $vatItemsTotal = $this->items()->where('has_vat', true)->sum('total_price');
        return $vatItemsTotal * 0.18; // 18% VAT
    }

    // Calculate subtotal (excluding VAT)
    public function calculateSubtotal()
    {
        return $this->items()->sum('total_price');
    }

     // Calculate total including VAT
    public function calculateTotal()
    {
        $subtotal = $this->calculateSubtotal();
        $vatAmount = $this->calculateVatAmount();
        return $subtotal + $vatAmount + ($this->other_charges ?? 0);
    }

    // Get items with VAT
    public function getItemsWithVat()
    {
        return $this->items()->where('has_vat', true)->get();
    }

    // Get items without VAT
    public function getItemsWithoutVat()
    {
        return $this->items()->where('has_vat', false)->get();
    }

    // Add an accessor for issue_date to maintain compatibility
    public function getIssueDateAttribute()
    {
        return $this->issued_at;
    }

    public function preparer()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    public function items()
    {
        return $this->hasMany(LpoItem::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function receivedItems()
    {
        return $this->hasMany(LpoReceivedItem::class);
    }

    public function getAmountInWords()
{
    $amount = $this->total;
    
    if ($amount == 0) return 'Zero Uganda Shillings Only';
    
    // Define arrays as class variables or pass them properly
    $units = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];
    $teens = ['Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    
    // Use closure with use() to pass the arrays
    $convertThreeDigits = function($num) use ($units, $teens, $tens) {
        $words = '';
        
        // Hundreds
        if ($num >= 100) {
            $hundreds = floor($num / 100);
            $words .= $units[$hundreds] . ' Hundred ';
            $num %= 100;
        }
        
        // Tens and units
        if ($num >= 20) {
            $tensDigit = floor($num / 10);
            $words .= $tens[$tensDigit] . ' ';
            $num %= 10;
        } else if ($num >= 10) {
            $words .= $teens[$num - 10] . ' ';
            $num = 0;
        }
        
        // Units
        if ($num > 0) {
            $words .= $units[$num] . ' ';
        }
        
        return trim($words);
    };
    
    $shillings = floor($amount);
    $cents = round(($amount - $shillings) * 100);
    
    $words = '';
    
    // Millions
    if ($shillings >= 1000000) {
        $millions = floor($shillings / 1000000);
        $words .= $convertThreeDigits($millions) . ' Million ';
        $shillings %= 1000000;
    }
    
    // Thousands
    if ($shillings >= 1000) {
        $thousands = floor($shillings / 1000);
        $words .= $convertThreeDigits($thousands) . ' Thousand ';
        $shillings %= 1000;
    }
    
    // Hundreds
    if ($shillings > 0) {
        $words .= $convertThreeDigits($shillings) . ' ';
    }
    
    if (empty(trim($words))) {
        $words = 'Zero ';
    }
    
    $words .= 'Uganda Shillings';
    
    // Cents
    if ($cents > 0) {
        $words .= ' and ' . $convertThreeDigits($cents) . ' Cents';
    }
    
    return $words . ' Only';
}
}