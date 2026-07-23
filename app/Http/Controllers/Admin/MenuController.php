<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function index(Request $request): Response
    {
        $role = $request->string('role')->value() ?: User::ROLE_FULL_ACCESS;
        $search = $request->string('search')->trim()->value();

        return Inertia::render('Admin/Menus/Index', [
            'items' => MenuItem::query()->where('role', $role)->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('href', 'like', "%{$search}%");
            }))->with('parent')->orderBy('sort_order')->orderBy('title')->paginate(30)->withQueryString(),
            'role' => $role,
            'roles' => User::roleLabels(),
            'search' => $search,
        ]);
    }

    public function create(Request $request): Response
    {
        $role = $request->string('role')->value() ?: User::ROLE_FULL_ACCESS;

        return Inertia::render('Admin/Menus/Form', [
            'item' => null,
            'role' => $role,
            'roles' => User::roleLabels(),
            'parents' => $this->parentOptions($role),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $item = MenuItem::create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Voce di menu creata.']);

        return to_route('admin.menus.edit', $item);
    }

    public function edit(MenuItem $menuItem): Response
    {
        return Inertia::render('Admin/Menus/Form', [
            'item' => $menuItem,
            'role' => $menuItem->role,
            'roles' => User::roleLabels(),
            'parents' => $this->parentOptions($menuItem->role, $menuItem->id),
        ]);
    }

    public function update(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $menuItem->update($this->validatedData($request, $menuItem));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Voce di menu aggiornata.']);

        return to_route('admin.menus.edit', $menuItem);
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Voce di menu eliminata.']);

        return to_route('admin.menus.index', ['role' => $menuItem->role]);
    }

    /** @return array<int, array{id: int, title: string, depth: int}> */
    private function parentOptions(string $role, ?int $except = null): array
    {
        $items = MenuItem::query()->where('role', $role)->when($except, fn ($query) => $query->where('id', '!=', $except))->orderBy('sort_order')->orderBy('title')->get();
        $options = [];

        foreach ($items as $item) {
            $options[] = ['id' => $item->id, 'title' => $item->title, 'depth' => 0];
        }

        return $options;
    }

    /** @return array<string, mixed> */
    private function validatedData(Request $request, ?MenuItem $item = null): array
    {
        return $request->validate([
            'role' => ['required', 'string', Rule::in(array_keys(User::roleLabels()))],
            'parent_id' => ['nullable', 'integer', Rule::exists('menu_items', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'href' => ['nullable', 'string', 'max:2048'],
            'icon' => ['nullable', 'string', 'max:80'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['boolean'],
        ]);
    }
}
