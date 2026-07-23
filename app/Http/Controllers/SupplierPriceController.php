<?php

namespace App\Http\Controllers;

use App\Models\SupplierPrice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupplierPriceController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        $prices = SupplierPrice::query()
            ->with(['ingredient:id,name,unit', 'supplier:id,name'])
            ->when($search, function ($query, string $search): void {
                $query->whereHas('ingredient', fn ($ingredient) => $ingredient->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('supplier', fn ($supplier) => $supplier->where('name', 'like', "%{$search}%"));
            })
            ->orderByDesc('is_current')
            ->orderByDesc('valid_from')
            ->paginate(30)
            ->withQueryString()
            ->through(fn (SupplierPrice $price): array => [
                'id' => $price->id,
                'ingredient' => $price->ingredient?->name,
                'ingredient_id' => $price->ingredient_id,
                'unit' => $price->ingredient?->unit,
                'supplier' => $price->supplier?->name,
                'package_name' => $price->package_name,
                'package_quantity' => $price->package_quantity,
                'package_unit' => $price->package_unit,
                'package_price' => $price->package_price,
                'valid_from' => $price->valid_from?->format('Y-m-d'),
                'is_current' => $price->is_current,
            ]);

        return Inertia::render('Ingredients/Prices', [
            'prices' => $prices,
            'search' => $search,
        ]);
    }
}
