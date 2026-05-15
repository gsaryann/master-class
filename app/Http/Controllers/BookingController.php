<?php

namespace App\Http\Controllers;

use App\Models\MasterClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function confirm(Request $request, MasterClass $masterClass): View|RedirectResponse
    {
        $user = $this->getCurrentUser($request);
        $masterClass->load(['craftType', 'master', 'participants']);

        if (! $user) {
            return redirect('/login')->withErrors(['auth' => 'Для записи нужно войти в систему.']);
        }

        if ($user->isMaster()) {
            return redirect('/')->withErrors(['auth' => 'Ведущий не записывается на мастер-классы.']);
        }

        if ($masterClass->is_expired) {
            return redirect()->route('craft-types.show', $masterClass->craftType)
                ->withErrors(['booking' => 'Запись на этот мастер-класс уже закрыта, потому что он завершился.']);
        }

        if ($masterClass->free_places < 1) {
            return redirect()->route('craft-types.show', $masterClass->craftType)
                ->withErrors(['booking' => 'Свободных мест больше нет.']);
        }

        if ($user->bookedMasterClasses()->where('master_classes.id', $masterClass->id)->exists()) {
            return redirect()->route('craft-types.show', $masterClass->craftType)
                ->withErrors(['booking' => 'Вы уже записаны на этот мастер-класс.']);
        }

        if ($this->hasBookingConflict($user, $masterClass)) {
            return redirect()->route('craft-types.show', $masterClass->craftType)
                ->withErrors(['booking' => 'У вас уже есть запись на другой мастер-класс в эту дату и время.']);
        }

        return view('booking.confirm', [
            'masterClass' => $masterClass,
            'user' => $user,
            'mode' => 'store',
            'source' => 'category',
        ]);
    }

    public function confirmCancel(Request $request, MasterClass $masterClass): View|RedirectResponse
    {
        $user = $this->getCurrentUser($request);

        if (! $user) {
            return redirect('/login')->withErrors(['auth' => 'Для отмены записи нужно войти в систему.']);
        }

        if ($user->isMaster()) {
            return redirect('/')->withErrors(['auth' => 'Ведущий не управляет записью на мастер-классы как участник.']);
        }

        if (! $user->bookedMasterClasses()->where('master_classes.id', $masterClass->id)->exists()) {
            return redirect()->route('craft-types.show', $masterClass->craftType)
                ->withErrors(['booking' => 'Запись на этот мастер-класс не найдена.']);
        }

        return view('booking.confirm', [
            'masterClass' => $masterClass,
            'user' => $user,
            'mode' => 'cancel',
            'source' => $request->query('source', 'category'),
        ]);
    }

    public function store(Request $request, MasterClass $masterClass): RedirectResponse
    {
        $user = $this->getCurrentUser($request);
        $masterClass->load(['craftType', 'participants']);

        if (! $user) {
            return redirect('/login')->withErrors(['auth' => 'Для записи нужно войти в систему.']);
        }

        if ($user->isMaster()) {
            return redirect('/')->withErrors(['auth' => 'Ведущий не записывается на мастер-классы.']);
        }

        if ($masterClass->is_expired) {
            return redirect()->route('craft-types.show', $masterClass->craftType)
                ->withErrors(['booking' => 'Запись на этот мастер-класс уже закрыта, потому что он завершился.']);
        }

        if ($user->bookedMasterClasses()->where('master_classes.id', $masterClass->id)->exists()) {
            return redirect()->route('craft-types.show', $masterClass->craftType)
                ->withErrors(['booking' => 'Вы уже записаны на этот мастер-класс.']);
        }

        if ($this->hasBookingConflict($user, $masterClass)) {
            return redirect()->route('craft-types.show', $masterClass->craftType)
                ->withErrors(['booking' => 'У вас уже есть запись на другой мастер-класс в эту дату и время.']);
        }

        $masterClass->loadCount('participants');

        if ($masterClass->participants_count >= $masterClass->max_people) {
            return redirect()->route('craft-types.show', $masterClass->craftType)
                ->withErrors(['booking' => 'Свободных мест больше нет.']);
        }

        $user->bookedMasterClasses()->attach($masterClass->id);

        return redirect()->route('craft-types.show', $masterClass->craftType)
            ->with('status', 'Запись на мастер-класс подтверждена.');
    }

    public function cancel(Request $request, MasterClass $masterClass): RedirectResponse
    {
        $user = $this->getCurrentUser($request);

        if (! $user) {
            return redirect('/login')->withErrors(['auth' => 'Для отмены записи нужно войти в систему.']);
        }

        if ($user->isMaster()) {
            return redirect('/')->withErrors(['auth' => 'Ведущий не управляет записью на мастер-классы как участник.']);
        }

        if (! $user->bookedMasterClasses()->where('master_classes.id', $masterClass->id)->exists()) {
            return $this->redirectAfterCancel($request, $masterClass)
                ->withErrors(['booking' => 'Запись на этот мастер-класс не найдена.']);
        }

        $user->bookedMasterClasses()->detach($masterClass->id);

        return $this->redirectAfterCancel($request, $masterClass)
            ->with('status', 'Запись на мастер-класс отменена.');
    }

    private function redirectAfterCancel(Request $request, MasterClass $masterClass): RedirectResponse
    {
        if ($request->input('source') === 'home') {
            return redirect()->route('home');
        }

        return redirect()->route('craft-types.show', $masterClass->craftType);
    }

    private function hasBookingConflict(User $user, MasterClass $masterClass): bool
    {
        return $user->bookedMasterClasses()
            ->where('master_classes.id', '!=', $masterClass->id)
            ->whereDate('scheduled_date', $masterClass->scheduled_date)
            ->where('start_time', $masterClass->start_time)
            ->exists();
    }
}
