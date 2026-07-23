<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPrice extends Model
{
    protected $fillable = [
        'supplier_id', 'supplier_code', 'package_name', 'package_quantity', 'package_unit',
        'package_price', 'valid_from', 'is_current', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'package_quantity' => 'decimal:3',
            'package_price' => 'decimal:4',
            'valid_from' => 'date',
            'is_current' => 'boolean',
        ];
    }

    /** @return BelongsTo<Ingredient, $this> */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    /** @return BelongsTo<Supplier, $this> */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
