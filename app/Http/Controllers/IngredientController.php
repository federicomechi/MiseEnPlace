<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class IngredientController extends Controller
{
    /** @var array<int, string> */
    private const UNITS = ['g', 'kg', 'ml', 'l', 'pz', 'cl', 'busta', 'vasetto'];

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'bar' => ['nullable', 'boolean'],
        ]);

        $ingredients = Ingredient::query()
            ->with(['supplierPrices' => fn ($query) => $query->where('is_current', true)->with('supplier')])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->when(($filters['bar'] ?? false) === true, fn ($query) => $query->where('available_for_bar', true))
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString()
            ->through(fn (Ingredient $ingredient): array => [
                'id' => $ingredient->id,
                'code' => $ingredient->code,
                'name' => $ingredient->name,
                'category' => $ingredient->category,
                'unit' => $ingredient->unit,
                'unit_cost' => $ingredient->unit_cost,
                'cost_date' => $ingredient->cost_date?->format('Y-m-d'),
                'available_for_bar' => $ingredient->available_for_bar,
                'supplier' => $ingredient->supplierPrices->first()?->supplier?->name,
            ]);

        return Inertia::render('Ingredients/Index', [
            'ingredients' => $ingredients,
            'filters' => ['search' => $filters['search'] ?? '', 'bar' => (bool) ($filters['bar'] ?? false)],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Ingredients/Form', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        $ingredient = DB::transaction(function () use ($data): Ingredient {
            $ingredient = Ingredient::create($this->ingredientData($data));
            $this->syncPrices($ingredient, $data['prices'] ?? []);

            return $ingredient;
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Ingrediente creato.']);

        return to_route('ingredients.edit', $ingredient);
    }

    public function edit(Ingredient $ingredient): Response
    {
        $ingredient->load('supplierPrices.supplier');

        return Inertia::render('Ingredients/Form', [
            ...$this->formData(),
            'ingredient' => [
                'id' => $ingredient->id,
                'code' => $ingredient->code,
                'name' => $ingredient->name,
                'category' => $ingredient->category,
                'unit' => $ingredient->unit,
                'notes' => $ingredient->notes,
                'available_for_bar' => $ingredient->available_for_bar,
                'prices' => $ingredient->supplierPrices->map(fn ($price): array => [
                    'id' => $price->id,
                    'supplier_id' => $price->supplier_id,
                    'supplier_code' => $price->supplier_code,
                    'package_name' => $price->package_name,
                    'package_quantity' => $price->package_quantity,
                    'package_unit' => $price->package_unit,
                    'package_price' => $price->package_price,
                    'valid_from' => $price->valid_from?->format('Y-m-d'),
                    'is_current' => $price->is_current,
                    'notes' => $price->notes,
                ])->values(),
            ],
        ]);
    }

    public function update(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $data = $this->validatedData($request, $ingredient);

        DB::transaction(function () use ($ingredient, $data): void {
            $ingredient->update($this->ingredientData($data));
            $this->syncPrices($ingredient, $data['prices'] ?? []);
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Ingrediente aggiornato.']);

        return to_route('ingredients.edit', $ingredient);
    }

    public function destroy(Ingredient $ingredient): RedirectResponse
    {
        abort_if($ingredient->recipeStepIngredients()->exists(), 422, 'L’ingrediente è utilizzato in una ricetta e non può essere eliminato.');

        $ingredient->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Ingrediente eliminato.']);

        return to_route('ingredients.index');
    }

    /** @return array<string, mixed> */
    private function formData(): array
    {
        return [
            'ingredient' => null,
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'units' => self::UNITS,
        ];
    }

    /** @return array<string, mixed> */
    private function validatedData(Request $request, ?Ingredient $ingredient = null): array
    {
        return $request->validate([
            'code' => ['nullable', 'string', 'max:64', Rule::unique('ingredients', 'code')->ignore($ingredient)],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:80'],
            'unit' => ['nullable', Rule::in(self::UNITS)],
            'notes' => ['nullable', 'string'],
            'available_for_bar' => ['boolean'],
            'prices' => ['array'],
            'prices.*.id' => ['nullable', 'integer'],
            'prices.*.supplier_id' => ['required', 'integer', Rule::exists('suppliers', 'id')],
            'prices.*.supplier_code' => ['nullable', 'string', 'max:64'],
            'prices.*.package_name' => ['nullable', 'string', 'max:255'],
            'prices.*.package_quantity' => ['nullable', 'numeric', 'gt:0'],
            'prices.*.package_unit' => ['nullable', Rule::in(self::UNITS)],
            'prices.*.package_price' => ['required', 'numeric', 'min:0'],
            'prices.*.valid_from' => ['required', 'date'],
            'prices.*.is_current' => ['boolean'],
            'prices.*.notes' => ['nullable', 'string'],
        ]);
    }

    /** @param array<string, mixed> $data */
    private function ingredientData(array $data): array
    {
        return [
            'code' => filled($data['code'] ?? null) ? $data['code'] : null,
            'name' => $data['name'],
            'category' => filled($data['category'] ?? null) ? $data['category'] : null,
            'unit' => $data['unit'] ?? null,
            'notes' => filled($data['notes'] ?? null) ? $data['notes'] : null,
            'available_for_bar' => $data['available_for_bar'] ?? false,
        ];
    }

    /** @param array<int, array<string, mixed>> $prices */
    private function syncPrices(Ingredient $ingredient, array $prices): void
    {
        $keptIds = collect($prices)->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();
        $ingredient->supplierPrices()->whereNotIn('id', $keptIds)->delete();

        foreach ($prices as $price) {
            $payload = [
                'supplier_id' => $price['supplier_id'],
                'supplier_code' => $price['supplier_code'] ?? null,
                'package_name' => $price['package_name'] ?? null,
                'package_quantity' => $price['package_quantity'] ?? null,
                'package_unit' => $price['package_unit'] ?? null,
                'package_price' => $price['package_price'],
                'valid_from' => $price['valid_from'],
                'is_current' => $price['is_current'] ?? false,
                'notes' => $price['notes'] ?? null,
            ];

            if (filled($price['id'] ?? null)) {
                if ($payload['is_current']) {
                    $ingredient->supplierPrices()->where('id', '!=', $price['id'])->update(['is_current' => false]);
                }

                $ingredient->supplierPrices()->whereKey($price['id'])->update($payload);
            } else {
                if ($payload['is_current']) {
                    $ingredient->supplierPrices()->update(['is_current' => false]);
                }

                $ingredient->supplierPrices()->create($payload);
            }
        }

        $current = $ingredient->supplierPrices()->where('is_current', true)->orderByDesc('valid_from')->first();
        $unitCost = $current && (float) $current->package_quantity > 0
            ? (float) $current->package_price / (float) $current->package_quantity
            : null;

        $ingredient->forceFill([
            'package_quantity' => $current?->package_quantity,
            'unit_cost' => $unitCost,
            'cost_date' => $current?->valid_from,
        ])->save();
    }
}
