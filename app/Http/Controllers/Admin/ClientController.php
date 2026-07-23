<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        return Inertia::render('Admin/Clients', [
            'clients' => Client::query()
                ->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('location_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                }))
                ->withCount('users')
                ->with(['users' => fn ($query) => $query->orderBy('name')->limit(3)])
                ->orderBy('name')
                ->get(['id', 'filemaker_id', 'name', 'location_id', 'location_name', 'email', 'expires_at']),
            'search' => $search,
        ]);
    }
}
