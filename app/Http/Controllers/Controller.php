<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

abstract class Controller
{
    protected function getCurrentUser(Request $request): ?User
    {
        $sessionUser = $request->session()->get('user');

        if (! $sessionUser || ! isset($sessionUser['id'])) {
            return null;
        }

        return User::find($sessionUser['id']);
    }

    protected function storeUserInSession(Request $request, User $user): void
    {
        $request->session()->put('user', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'photo' => $user->photo,
        ]);
    }
}
