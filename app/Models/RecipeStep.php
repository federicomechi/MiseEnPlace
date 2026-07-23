<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecipeStep extends Model
{
    use BelongsToClient;

    protected $fillable = [
        'client_id', 'filemaker_id', 'recipe_id', 'sort_order', 'name', 'description',
        'humidity', 'temperature', 'duration_minutes',
    ];

    /** @return BelongsTo<Recipe, $this> */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /** @return HasMany<RecipeStepIngredient, $this> */
    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeStepIngredient::class);
    }
}
