<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    protected $fillable = [
        'filemaker_id', 'source_created_at', 'name', 'print_name', 'tag',
        'yield_quantity', 'yield_unit', 'multiplier_quantity', 'presentation',
        'season', 'total_minutes', 'preparation_minutes', 'cooking_minutes',
        'shelf_life_days', 'storage_instructions', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'source_created_at' => 'date',
            'yield_quantity' => 'decimal:3',
            'multiplier_quantity' => 'decimal:3',
        ];
    }

    /** @return HasMany<RecipeStep, $this> */
    public function steps(): HasMany
    {
        return $this->hasMany(RecipeStep::class)->orderBy('sort_order');
    }
}
