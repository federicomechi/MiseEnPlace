<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        return Inertia::render('Ingredients/Suppliers', [
            'suppliers' => Supplier::query()
                ->withCount('prices')
                ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
                ->orderBy('name')
                ->get(),
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Supplier::create($this->validatedData($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Fornitore creato.']);

        return to_route('suppliers.index');
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($this->validatedData($request, $supplier));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Fornitore aggiornato.']);

        return to_route('suppliers.index');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        abort_if($supplier->prices()->exists(), 422, 'Il fornitore ha prezzi associati e non può essere eliminato.');

        $supplier->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Fornitore eliminato.']);

        return to_route('suppliers.index');
    }

    /** @return array<string, mixed> */
    private function validatedData(Request $request, ?Supplier $supplier = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('suppliers', 'name')->ignore($supplier)],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:64'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);
    }
}
