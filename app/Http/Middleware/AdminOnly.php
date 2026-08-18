<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Restrict access to admin-only routes.
 * Kepala Sekolah users will be redirected to their own dashboard.
 */
class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        if (!Auth::user()->isAdmin()) {
            return redirect()->route('kepsek.dashboard')
                ->with('error', 'Akses ditolak. Anda tidak memiliki izin Admin.');
        }

        return $next($request);
    }
}
