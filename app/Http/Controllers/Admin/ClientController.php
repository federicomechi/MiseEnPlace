<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Clients', [
            'clients' => Client::query()
                ->withCount('users')
                ->with(['users' => fn ($query) => $query->orderBy('name')->limit(3)])
                ->orderBy('name')
                ->get(['id', 'filemaker_id', 'name', 'location_id', 'location_name', 'email', 'expires_at']),
        ]);
    }
}
