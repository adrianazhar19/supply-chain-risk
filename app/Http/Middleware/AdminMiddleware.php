<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Jika belum login, arahkan ke halaman login admin
        if (! Auth::check()) {
            return redirect()->route('admin.login');
        }

        // Jika sudah login tapi bukan admin, kembalikan 403 Forbidden
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Forbidden: Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}