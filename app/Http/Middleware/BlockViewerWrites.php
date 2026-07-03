<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// "viewer" accounts (from the shared users table) are read-only: they can
// browse every screen but are blocked from any state-changing request
// (POST/PUT/PATCH/DELETE). One guard here covers every write route at once.
class BlockViewerWrites
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isViewer() && ! $request->isMethodSafe()) {
            abort(403, 'حسابك للاطّلاع فقط ولا يسمح بإجراء تعديلات.');
        }

        return $next($request);
    }
}
