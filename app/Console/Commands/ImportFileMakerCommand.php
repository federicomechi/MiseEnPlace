<?php

namespace App\Console\Commands;

use App\Models\Ingredient;
use App\Models\Supplier;
use App\Models\SupplierPrice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportFileMakerCommand extends Command
{
    protected $signature = 'filemaker:import {--path= : Directory containing FileMaker CSV exports} {--only=ingredients : Import target: ingredients}';

    protected $description = 'Importa ingredienti, fornitori e listini dagli export CSV FileMaker';

    public function handle(): int
    {
        $path = $this->option('path') ?: storage_path('app/private/filemaker-import');
        $suppliers = $this->importSuppliers($path);
        $this->importIngredients($path, $suppliers);

        $this->info('Importazione ingredienti, fornitori e listini completata.');

        return self::SUCCESS;
    }

    /** @return array<int|string, Supplier> */
    private function importSuppliers(string $path): array
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
                ['filemaker_id' => $existingWithKey === null || $existingWithKey->name === $name ? $supplierKey : null, 'email' => $this->nullable($row[0] ?? null), 'phone' => $this->nullable($row[5] ?? null), 'notes' => $this->nullable($row[4] ?? null), 'is_active' => true],
            );
            if ($supplierKey !== null) {
                $result[$supplierKey] = $supplier;
            }
            $result[$this->supplierKey($name)] = $supplier;
        }

        return $result;
    }

    /** @param array<int|string, Supplier> $suppliers */
    private function importIngredients(string $path, array $suppliers): void
    {
        foreach ($this->rows($path.'/DB_ingredienti.csv') as $row) {
            $filemakerId = $this->integer($row[7] ?? null);
            $name = trim($row[9] ?? '');
            if ($filemakerId === null || $name === '') {
                continue;
            }

            DB::transaction(function () use ($row, $filemakerId, $name, $suppliers): void {
                $ingredient = Ingredient::query()->updateOrCreate(
                    ['filemaker_id' => $filemakerId],
                    ['name' => $name, 'unit' => $this->nullable($row[12] ?? null), 'package_quantity' => $this->decimal($row[11] ?? null), 'unit_cost' => $this->decimal($row[3] ?? null), 'cost_date' => $this->date($row[4] ?? null), 'notes' => $this->nullable($row[10] ?? null), 'available_for_bar' => trim($row[5] ?? '') !== ''],
                );

                $supplier = $suppliers[$this->supplierKey($row[6] ?? '')] ?? null;
                $price = $this->decimal($row[2] ?? null);
                $date = $this->date($row[4] ?? null);
                if ($supplier && $price !== null && $date !== null) {
                    SupplierPrice::query()->updateOrCreate(
                        ['ingredient_id' => $ingredient->id, 'supplier_id' => $supplier->id, 'valid_from' => $date],
                        ['package_quantity' => $this->decimal($row[11] ?? null), 'package_unit' => $this->nullable($row[12] ?? null), 'package_price' => $price, 'is_current' => true],
                    );
                }
            });
        }
    }

    /** @return iterable<int, array<int, string>> */
    private function rows(string $file): iterable
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

    private function date(?string $value): ?string
    {
        $value = $this->nullable($value);

        return $value === null ? null : Carbon::createFromFormat('d/m/Y', $value)?->format('Y-m-d');
    }
}
