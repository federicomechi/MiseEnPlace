<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecipeController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();
        $recipes = Recipe::query()
            ->withCount('steps')
            ->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('tag', 'like', "%{$search}%")
                    ->orWhere('season', 'like', "%{$search}%");
            }))
            ->orderBy('name')->paginate(24)->withQueryString();

        return Inertia::render('Recipes/Index', ['recipes' => $recipes, 'search' => $search]);
    }

    public function create(): Response
    {
        return Inertia::render('Recipes/Form', ['recipe' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $recipe = Recipe::create($this->data($request));
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Ricetta creata.']);

        return to_route('recipes.edit', $recipe);
    }

    public function edit(Recipe $recipe): Response
    {
        $recipe->load(['steps.ingredients.ingredient']);

        return Inertia::render('Recipes/Form', ['recipe' => $recipe]);
    }

    public function update(Request $request, Recipe $recipe): RedirectResponse
    {
        $recipe->update($this->data($request, $recipe));
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Ricetta aggiornata.']);

        return to_route('recipes.edit', $recipe);
    }

    public function destroy(Recipe $recipe): RedirectResponse
    {
        $recipe->delete();
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Ricetta eliminata.']);

        return to_route('recipes.index');
    }

    /** @return array<string, mixed> */
    private function data(Request $request, ?Recipe $recipe = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'print_name' => ['nullable', 'string', 'max:255'],
            'tag' => ['nullable', 'string', 'max:255'],
            'season' => ['nullable', 'string', 'max:255'],
            'yield_quantity' => ['nullable', 'numeric', 'gt:0'],
            'yield_unit' => ['nullable', 'string', 'max:32'],
            'total_minutes' => ['nullable', 'integer', 'min:0'],
            'preparation_minutes' => ['nullable', 'integer', 'min:0'],
            'cooking_minutes' => ['nullable', 'integer', 'min:0'],
            'shelf_life_days' => ['nullable', 'integer', 'min:0'],
            'presentation' => ['nullable', 'string'],
            'storage_instructions' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($recipe === null) {
            $data['filemaker_id'] = ((int) Recipe::withoutGlobalScopes()->max('filemaker_id')) + 1;
        }

        return $data;
    }
}
