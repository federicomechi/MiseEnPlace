<?php

namespace App\Models;

use App\Models\Concerns\BelongsToClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeStepIngredient extends Model
{
    use BelongsToClient;

    protected $fillable = [
        'client_id', 'filemaker_id', 'recipe_step_id', 'ingredient_id', 'quantity', 'unit', 'notes',
    ];

    /** @return BelongsTo<RecipeStep, $this> */
    public function recipeStep(): BelongsTo
    {
        return $this->belongsTo(RecipeStep::class);
    }

    /** @return BelongsTo<Ingredient, $this> */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
