<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessWorkspaceSection
{
    public function handle(Request $request, Closure $next): Response
    {
        $section = (string) $request->route('section');

        if ($section === '' && $request->is('operativita/ingredients*')) {
            $section = 'ingredients';
        }

        abort_unless(
            $request->user()?->hasAdministrativeAccess()
                || in_array($section, $request->user()?->permittedWorkspaceSections() ?? [], true),
            Response::HTTP_FORBIDDEN,
        );

        return $next($request);
    }
}
