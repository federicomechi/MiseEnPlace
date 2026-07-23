<?php

namespace App\Http\Controllers;

use App\Models\Allergen;
use App\Models\Equipment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReferenceCatalogController extends Controller
{
    /** @var array<string, class-string<Model>> */
    private const MODELS = ['allergens' => Allergen::class, 'equipment' => Equipment::class];

    public function index(Request $request, string $section): Response
    {
        $model = $this->model($section);
        $search = $request->string('search')->trim()->value();
        $items = $model::query()->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
            $query->where('name', 'like', "%{$search}%")->orWhere('category', 'like', "%{$search}%");
        }))->orderBy('name')->get();

        return Inertia::render('ReferenceCatalog/Index', ['section' => $section, 'title' => $section === 'allergens' ? 'Allergeni' : 'Attrezzature e supporti', 'items' => $items, 'search' => $search]);
    }

    public function store(Request $request, string $section): RedirectResponse
    {
        $this->model($section)::create($this->data($request));

        return to_route('reference.index', $section);
    }

    public function update(Request $request, string $section, int $item): RedirectResponse
    {
        $this->model($section)::query()->findOrFail($item)->update($this->data($request));

        return to_route('reference.index', $section);
    }

    public function destroy(string $section, int $item): RedirectResponse
    {
        $this->model($section)::query()->findOrFail($item)->delete();

        return to_route('reference.index', $section);
    }

    /** @return class-string<Model> */
    private function model(string $section): string
    {
        abort_unless(array_key_exists($section, self::MODELS), 404);

        return self::MODELS[$section];
    }

    /** @return array<string, mixed> */
    private function data(Request $request): array
    {
        return $request->validate(['name' => ['required', 'string', 'max:255'], 'code' => ['nullable', 'string', 'max:64'], 'category' => ['nullable', 'string', 'max:100'], 'description' => ['nullable', 'string'], 'is_active' => ['boolean']]);
    }
}
