<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionUser
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('user')) {
            return redirect()->route('login')
                ->withErrors(['auth' => 'Для продолжения нужно войти в систему.']);
        }

        return $next($request);
    }
}
