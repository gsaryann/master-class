<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionGuest
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sessionUser = $request->session()->get('user');

        if (is_array($sessionUser) && isset($sessionUser['role'])) {
            if ($sessionUser['role'] === User::ROLE_MASTER) {
                return redirect()->route('cabinet');
            }

            return redirect()->route('home');
        }

        return $next($request);
    }
}
