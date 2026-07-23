<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/** @property Carbon|null $valid_from */
class SupplierPrice extends Model
{
    use BelongsToClient;

    protected $fillable = [
        'client_id', 'ingredient_id', 'supplier_id', 'supplier_code', 'package_name', 'package_quantity', 'package_unit',
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
