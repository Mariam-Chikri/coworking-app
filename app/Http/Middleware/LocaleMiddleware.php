<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LocaleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        app()->setLocale(session('locale', 'fr'));

        return $next($request);
    }
}