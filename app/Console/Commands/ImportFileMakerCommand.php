<?php

namespace App\Console\Commands;

use App\Models\Allergen;
use App\Models\Client;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeStep;
use App\Models\RecipeStepIngredient;
use App\Models\Supplier;
use App\Models\SupplierPrice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportFileMakerCommand extends Command
{
    protected $signature = 'filemaker:import {--path= : Directory containing FileMaker CSV exports} {--only=all : Import target: all, recipes, ingredients or allergens}';

    protected $description = 'Importa ingredienti, fornitori e listini dagli export CSV FileMaker';

    public function handle(): int
    {
        $path = $this->option('path') ?: storage_path('app/private/filemaker-import');
        $client = $this->importClient($path);
        $only = $this->option('only');
        $suppliers = $this->importSuppliers($path, $client);
        if (in_array($only, ['all', 'ingredients'], true)) {
            $this->importIngredients($path, $suppliers, $client);
        }
        if (in_array($only, ['all', 'recipes'], true)) {
            $this->importRecipes($path, $client);
        }
        if (in_array($only, ['all', 'allergens'], true)) {
            $this->importAllergens($path, $client);
        }
        foreach (['users', 'recipes', 'ingredients', 'recipe_steps', 'recipe_step_ingredients', 'suppliers', 'supplier_prices', 'development_entries', 'menu_items'] as $table) {
            DB::table($table)->whereNull('client_id')->update(['client_id' => $client->id]);
        }

        $this->info('Importazione FileMaker completata.');

        return self::SUCCESS;
    }

    private function importClient(string $path): Client
    {
        $rows = $this->rows($path.'/DB_cliente.csv');
        $row = $rows->current();

        return Client::query()->updateOrCreate(
            ['filemaker_id' => $this->nullable($row[0] ?? null)],
            ['name' => $this->nullable($row[2] ?? null) ?? 'Azienda FileMaker', 'location_id' => $this->nullable($row[3] ?? null), 'location_name' => $this->nullable($row[4] ?? null), 'email' => $this->nullable($row[6] ?? null), 'expires_at' => $this->date($row[5] ?? null)],
        );
    }

    /** @return array<int|string, Supplier> */
    private function importSuppliers(string $path, Client $client): array
    {
        $result = [];
        foreach ($this->rows($path.'/DB_fornitori.csv') as $row) {
            $name = trim($row[2] ?? '');
            if ($name === '') {
                continue;
            }

            $supplierKey = $this->integer($row[1] ?? null);
            $existingWithKey = $supplierKey !== null ? Supplier::query()->where('filemaker_id', $supplierKey)->first() : null;
            $supplier = Supplier::query()->updateOrCreate(
                ['name' => $name],
                ['client_id' => $client->id, 'filemaker_id' => $existingWithKey === null || $existingWithKey->name === $name ? $supplierKey : null, 'email' => $this->nullable($row[0] ?? null), 'phone' => $this->nullable($row[5] ?? null), 'notes' => $this->nullable($row[4] ?? null), 'is_active' => true],
            );
            if ($supplierKey !== null) {
                $result[$supplierKey] = $supplier;
            }
            $result[$this->supplierKey($name)] = $supplier;
        }

        return $result;
    }

    /** @param array<int|string, Supplier> $suppliers */
    private function importIngredients(string $path, array $suppliers, Client $client): void
    {
        foreach ($this->rows($path.'/DB_ingredienti.csv') as $row) {
            $filemakerId = $this->integer($row[7] ?? null);
            $name = trim($row[9] ?? '');
            if ($filemakerId === null || $name === '') {
                continue;
            }

            DB::transaction(function () use ($row, $filemakerId, $name, $suppliers, $client): void {
                $ingredient = Ingredient::query()->updateOrCreate(
                    ['filemaker_id' => $filemakerId],
                    ['client_id' => $client->id, 'name' => $name, 'unit' => $this->nullable($row[12] ?? null), 'package_quantity' => $this->decimal($row[11] ?? null), 'unit_cost' => $this->decimal($row[3] ?? null), 'cost_date' => $this->date($row[4] ?? null), 'notes' => $this->nullable($row[10] ?? null), 'available_for_bar' => trim($row[5] ?? '') !== ''],
                );

                $supplier = $suppliers[$this->supplierKey($row[6] ?? '')] ?? null;
                $price = $this->decimal($row[2] ?? null);
                $date = $this->date($row[4] ?? null);
                if ($supplier && $price !== null && $date !== null) {
                    SupplierPrice::query()->updateOrCreate(
                        ['ingredient_id' => $ingredient->id, 'supplier_id' => $supplier->id, 'valid_from' => $date],
                        ['client_id' => $client->id, 'package_quantity' => $this->decimal($row[11] ?? null), 'package_unit' => $this->nullable($row[12] ?? null), 'package_price' => $price, 'is_current' => true],
                    );
                }
            });
        }
    }

    private function importRecipes(string $path, Client $client): void
    {
        $recipes = [];
        foreach ($this->rows($path.'/DB_Ricette.csv') as $row) {
            $filemakerId = $this->integer($row[0] ?? null);
            $name = trim($row[2] ?? '');
            if ($filemakerId === null || $name === '') {
                continue;
            }

            $recipeData = $this->json($row[21] ?? null);
            $recipe = Recipe::query()->updateOrCreate(
                ['filemaker_id' => $filemakerId],
                [
                    'client_id' => $client->id,
                    'source_created_at' => $this->date($row[1] ?? null),
                    'name' => $this->nullable($recipeData['nome'] ?? null) ?? $name,
                    'print_name' => $this->nullable($recipeData['nome_stmp'] ?? ($row[3] ?? null)),
                    'tag' => $this->nullable($recipeData['tag'] ?? null),
                    'yield_quantity' => $this->decimal($recipeData['qta'] ?? null),
                    'yield_unit' => $this->nullable($recipeData['um'] ?? null),
                    'multiplier_quantity' => $this->decimal($recipeData['qta_multi'] ?? null),
                    'presentation' => $this->nullable($recipeData['presentazione'] ?? null),
                    'season' => $this->nullable($recipeData['stagione'] ?? null),
                    'total_minutes' => $this->minutes($recipeData['tempo'] ?? null),
                    'preparation_minutes' => $this->minutes($recipeData['tempo_prep'] ?? null),
                    'cooking_minutes' => $this->minutes($recipeData['tempo_cott'] ?? null),
                    'shelf_life_days' => $this->integer($recipeData['shelf_life'] ?? null),
                    'storage_instructions' => $this->nullable($recipeData['conservazione'] ?? null),
                    'notes' => $this->nullable($recipeData['note'] ?? null),
                ],
            );
            $recipes[$filemakerId] = $recipe;
        }

        $steps = [];
        foreach ($this->rows($path.'/DB_Fasi.csv') as $row) {
            $filemakerId = $this->integer($row[1] ?? null);
            $recipeFilemakerId = $this->integer($row[0] ?? null);
            $recipe = $recipeFilemakerId !== null ? ($recipes[$recipeFilemakerId] ?? Recipe::query()->where('filemaker_id', $recipeFilemakerId)->first()) : null;
            if ($filemakerId === null || $recipe === null) {
                continue;
            }

            $step = RecipeStep::query()->updateOrCreate(
                ['filemaker_id' => $filemakerId],
                [
                    'client_id' => $client->id,
                    'recipe_id' => $recipe->id,
                    'sort_order' => $this->integer($row[4] ?? null) ?? 0,
                    'name' => $this->nullable($row[5] ?? null),
                    'description' => $this->nullable($row[6] ?? null),
                    'humidity' => $this->decimal($row[7] ?? null),
                    'temperature' => $this->decimal($row[8] ?? null),
                    'duration_minutes' => $this->minutes($row[9] ?? null),
                ],
            );
            $steps[$filemakerId] = $step;
        }

        foreach ($this->rows($path.'/DB_FAS_Ingredienti.csv') as $row) {
            $filemakerId = $this->integer($row[0] ?? null);
            $stepFilemakerId = $this->integer($row[1] ?? null);
            $ingredientFilemakerId = $this->integer($row[4] ?? null);
            $step = $stepFilemakerId !== null ? ($steps[$stepFilemakerId] ?? RecipeStep::query()->where('filemaker_id', $stepFilemakerId)->first()) : null;
            $ingredient = $ingredientFilemakerId !== null ? Ingredient::query()->withoutGlobalScopes()->where('filemaker_id', $ingredientFilemakerId)->first() : null;
            if ($filemakerId === null || $step === null || $ingredient === null) {
                continue;
            }

            RecipeStepIngredient::query()->updateOrCreate(
                ['filemaker_id' => $filemakerId],
                [
                    'client_id' => $client->id,
                    'recipe_step_id' => $step->id,
                    'ingredient_id' => $ingredient->id,
                    'quantity' => $this->decimal($row[12] ?? null),
                    'unit' => $this->nullable($row[7] ?? null),
                    'notes' => $this->nullable($row[10] ?? null),
                ],
            );
        }
    }

    private function importAllergens(string $path, Client $client): void
    {
        foreach ($this->rows($path.'/DB_allergeni.csv') as $row) {
            $code = $this->integer($row[2] ?? null);
            $name = trim($row[0] ?? '');
            if ($code === null || $name === '') {
                continue;
            }

            Allergen::query()->updateOrCreate(
                ['client_id' => $client->id, 'code' => (string) $code],
                ['name' => preg_replace('/^\d+\s*-\s*/', '', $name) ?: $name, 'description' => $this->nullable($row[1] ?? null), 'is_active' => true],
            );
        }
    }

    /** @return \Generator<int, array<int, string>> */
    private function rows(string $file): \Generator
    {
        $contents = file_get_contents($file);
        foreach (preg_split("/\r\n|\r|\n/", $contents ?: '') ?: [] as $line) {
            if (trim($line) !== '') {
                $row = str_getcsv($line);
                if (count($row) > 1) {
                    yield array_map(static fn ($value): string => trim((string) $value), $row);
                }
            }
        }
    }

    private function nullable(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' || $value === '?' ? null : $value;
    }

    private function supplierKey(string $value): string
    {
        return preg_replace('/\s+/u', ' ', str_replace(["\x0b", "\x1d"], ' ', trim($value))) ?? trim($value);
    }

    private function integer(?string $value): ?int
    {
        $value = $this->nullable($value);

        return $value !== null && is_numeric($value) ? (int) $value : null;
    }

    private function decimal(?string $value): ?string
    {
        $value = $this->nullable($value);

        return $value === null ? null : str_replace(',', '.', $value);
    }

    /** @return array<string, mixed> */
    private function json(?string $value): array
    {
        $decoded = json_decode(trim((string) $value), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function minutes(?string $value): ?int
    {
        $value = $this->nullable($value);
        if ($value === null) {
            return null;
        }
        if (preg_match('/^(\d+):(\d+):(?:\d+)$/', $value, $matches) === 1) {
            return ((int) $matches[1] * 60) + (int) $matches[2];
        }
        if (preg_match('/(\d+)/', $value, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }

    private function date(?string $value): ?string
    {
        $value = $this->nullable($value);

        return $value === null ? null : Carbon::createFromFormat('d/m/Y', $value)?->format('Y-m-d');
    }
}
