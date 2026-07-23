<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        return Inertia::render('Admin/Users', [
            'users' => User::query()
                ->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                }))
                ->orderByDesc('is_admin')
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'email_verified_at', 'is_admin', 'role', 'created_at']),
            'search' => $search,
            'roleOptions' => collect(User::roleLabels())
                ->map(fn (string $label, string $value): array => ['label' => $label, 'value' => $value])
                ->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['nullable', 'string', Rule::in(array_keys(User::roleLabels()))],
        ]);

        $role = $data['role'] ?? User::ROLE_OPEN;

        $user = User::create([
            'client_id' => $request->user()->client_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $role,
            'is_admin' => User::isAdministrativeRole($role),
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Utente creato.']);

        return to_route('admin.users.index');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['nullable', 'string', Rule::in(array_keys(User::roleLabels()))],
        ]);

        $role = $data['role'] ?? $user->role;

        if ($request->user()->is($user) && ! User::isAdministrativeRole($role)) {
            return back()->withErrors(['role' => 'Non puoi revocare il tuo accesso amministrativo.']);
        }

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $role,
            'is_admin' => User::isAdministrativeRole($role),
        ]);

        if (filled($data['password'] ?? null)) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Utente aggiornato.']);

        return to_route('admin.users.index');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors(['users' => 'Non puoi eliminare il tuo account.']);
        }

        if ($user->hasAdministrativeAccess() && User::query()->whereIn('role', [User::ROLE_FULL_ACCESS, User::ROLE_EART_ADMIN])->count() <= 1) {
            return back()->withErrors(['users' => 'Deve rimanere almeno un amministratore.']);
        }

        $user->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Utente eliminato.']);

        return to_route('admin.users.index');
    }
}
