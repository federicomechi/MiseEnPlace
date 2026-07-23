<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DevelopmentEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DevelopmentController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        return Inertia::render('Admin/Development/Index', [
            'entries' => DevelopmentEntry::query()
                ->when($search, function ($query) use ($search): void {
                    $query->where(function ($query) use ($search): void {
                        $query->where('title', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhere('link', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Development/Form', ['entry' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $entry = DevelopmentEntry::create($this->validatedData($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Voce di sviluppo creata.']);

        return to_route('admin.development.edit', $entry);
    }

    public function edit(DevelopmentEntry $developmentEntry): Response
    {
        return Inertia::render('Admin/Development/Form', ['entry' => $developmentEntry]);
    }

    public function update(Request $request, DevelopmentEntry $developmentEntry): RedirectResponse
    {
        $developmentEntry->update($this->validatedData($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Voce di sviluppo aggiornata.']);

        return to_route('admin.development.edit', $developmentEntry);
    }

    public function destroy(DevelopmentEntry $developmentEntry): RedirectResponse
    {
        $developmentEntry->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Voce di sviluppo eliminata.']);

        return to_route('admin.development.index');
    }

    /** @return array<string, mixed> */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'link' => ['nullable', 'string', 'max:2048', 'url:http,https'],
        ]);
    }
}
