<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    protected function prepareAuthenticatedUser(User $user): User
    {
        if ($user->isMaster()) {
            $user->bookedMasterClasses()->detach();
        }

        return $user->fresh() ?? $user;
    }

    protected function checkAlreadyLoggedIn(Request $request): ?RedirectResponse
    {
        if ($request->session()->has('user')) {
            $user = $this->getCurrentUser($request);

            if ($user) {
                $user = $this->prepareAuthenticatedUser($user);
                $this->storeUserInSession($request, $user);
            }

            if ($user?->isMaster()) {
                return redirect()->route('cabinet')->with('status', 'Вы уже авторизованы.');
            }

            return redirect('/')->with('status', 'Вы уже авторизованы.');
        }

        return null;
    }

    public function showLogin(Request $request): View|RedirectResponse
    {
        $redirect = $this->checkAlreadyLoggedIn($request);

        if ($redirect) {
            return $redirect;
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Введите email.',
            'email.email' => 'Введите корректный email.',
            'password.required' => 'Введите пароль.',
        ], [
            'email' => 'email',
            'password' => 'пароль',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'Неверный email или пароль.'])
                ->withInput($request->except('password'));
        }

        $user = $this->prepareAuthenticatedUser($user);

        $request->session()->regenerate();
        $this->storeUserInSession($request, $user);

        if ($user->isMaster()) {
            return redirect()->route('cabinet')->with('status', 'Вы успешно вошли.');
        }

        return redirect('/')->with('status', 'Вы успешно вошли.');
    }

    public function showRegister(Request $request): View|RedirectResponse
    {
        $redirect = $this->checkAlreadyLoggedIn($request);

        if ($redirect) {
            return $redirect;
        }

        return view('form__reg');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[^\d]+$/u'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'phone' => ['required', 'regex:/^\+7\d{10}$/', 'unique:users,phone'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required' => 'Введите ФИО.',
            'name.max' => 'ФИО не должно быть длиннее 255 символов.',
            'name.regex' => 'ФИО не должно содержать цифры.',
            'email.required' => 'Введите email.',
            'email.email' => 'Введите корректный email.',
            'email.unique' => 'Пользователь с таким email уже зарегистрирован.',
            'password.required' => 'Введите пароль.',
            'password.min' => 'Пароль должен содержать минимум 6 символов.',
            'password.confirmed' => 'Пароли не совпадают.',
            'phone.required' => 'Введите номер телефона.',
            'phone.regex' => 'Телефон должен быть в формате +79000000000.',
            'phone.unique' => 'Пользователь с таким телефоном уже зарегистрирован.',
            'photo.image' => 'Файл фотографии должен быть изображением.',
            'photo.mimes' => 'Фотография должна быть в формате jpg, jpeg, png или webp.',
            'photo.max' => 'Размер фотографии не должен превышать 2 МБ.',
        ], [
            'name' => 'ФИО',
            'email' => 'email',
            'password' => 'пароль',
            'phone' => 'телефон',
            'photo' => 'фотография',
        ]);

        $photoPath = null;

        if ($request->hasFile('photo')) {
            $directory = public_path('uploads/users');

            if (! is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            $file = $request->file('photo');
            $fileName = Str::uuid().'.'.$file->getClientOriginalExtension();
            $file->move($directory, $fileName);
            $photoPath = 'uploads/users/'.$fileName;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => User::ROLE_VISITOR,
            'photo' => $photoPath,
            'password' => Hash::make($validated['password']),
        ]);

        $request->session()->regenerate();
        $this->storeUserInSession($request, $user);

        return redirect('/')->with('status', 'Регистрация прошла успешно.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('user');

        return redirect('/')->with('status', 'Вы вышли из аккаунта.');
    }
}
