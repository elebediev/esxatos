<?php

namespace App\Http\Middleware;

use App\Models\UrlRedirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectOldUrls
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = ltrim($request->path(), '/');

        // Check if this is an old URL that needs redirecting
        $redirect = UrlRedirect::findByPath($path);

        if ($redirect) {
            $redirect->recordHit();
            return redirect($redirect->new_path, $redirect->status_code);
        }

        return $next($request);
    }
}
