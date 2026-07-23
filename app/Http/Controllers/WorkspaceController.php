<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceController extends Controller
{
    /** @var array<string, string> */
    private const SECTIONS = [
        'recipes' => 'Ricette',
        'ingredients' => 'Ingredienti',
        'menus' => 'Menu',
        'production' => 'Produzione',
        'bar' => 'Bar e bevande',
        'suppliers' => 'Listini e fornitori',
        'settings' => 'Impostazioni operative',
        'setup' => 'Configurazione iniziale',
        'allergens' => 'Allergeni',
        'equipment' => 'Attrezzature e supporti',
    ];

    public function __invoke(Request $request, string $section): Response
    {
        abort_unless(array_key_exists($section, self::SECTIONS), 404);

        return Inertia::render('Workspace/Index', [
            'section' => $section,
            'title' => self::SECTIONS[$section],
            'roleLabel' => $request->user()->roleLabels()[$request->user()->role],
        ]);
    }
}
