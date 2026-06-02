<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    private const SESSION_TTL = 7200; // 2 hours

    public function handle(Request $request, Closure $next): mixed
    {
        if (! $request->session()->get('admin_authed')) {
            return redirect()->route('login');
        }

        $authedAt = $request->session()->get('admin_authed_at', 0);
        if ((time() - $authedAt) > self::SESSION_TTL) {
            $request->session()->forget(['admin_authed', 'admin_authed_at']);
            return redirect()->route('login')->with('error', 'Session expired. Please login again.');
        }

        $request->session()->put('admin_authed_at', time());
        return $next($request);
    }
}
