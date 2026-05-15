<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionMaster
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sessionUser = $request->session()->get('user');

        if (! is_array($sessionUser) || ($sessionUser['role'] ?? null) !== User::ROLE_MASTER) {
            return redirect()->route('home')
                ->withErrors(['auth' => 'Доступ разрешен только ведущему.']);
        }

        return $next($request);
    }
}
