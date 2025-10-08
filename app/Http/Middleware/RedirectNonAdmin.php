<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RedirectNonAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && !$user->can('view-dashboard')) {
            if ($user->role === 'produksi') {
                return redirect('/production');
            }
            if ($user->role === 'pengiriman') {
                return redirect('/transaction');
            }
        }
        
        return $next($request);
    }
}