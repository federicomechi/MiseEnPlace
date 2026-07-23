<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/** @property Carbon|null $cost_date */
class Ingredient extends Model
{
    protected $fillable = [
        'filemaker_id', 'code', 'name', 'category', 'unit', 'package_quantity', 'unit_cost',
        'cost_date', 'notes', 'available_for_bar',
    ];

    protected function casts(): array
    {
        return [
            'package_quantity' => 'decimal:3',
            'unit_cost' => 'decimal:4',
            'cost_date' => 'date',
            'available_for_bar' => 'boolean',
        ];
    }

    /** @return HasMany<RecipeStepIngredient, $this> */
    public function recipeStepIngredients(): HasMany
    {
        return $this->hasMany(RecipeStepIngredient::class);
    }

    /** @return HasMany<SupplierPrice, $this> */
    public function supplierPrices(): HasMany
    {
        return $this->hasMany(SupplierPrice::class)->orderByDesc('is_current')->orderByDesc('valid_from');
    }
}
