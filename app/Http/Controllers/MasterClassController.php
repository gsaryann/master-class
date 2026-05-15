<?php

namespace App\Http\Controllers;

use App\Models\CraftType;
use App\Models\MasterClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MasterClassController extends Controller
{
    public function index(Request $request): View
    {
        $sessionUser = $request->session()->get('user');
        $craftTypes = CraftType::orderBy('name')->get();
        $userBookings = $this->userBookings($request);
        $upcomingMasterClasses = MasterClass::query()
            ->with(['master', 'craftType', 'participants'])
            ->where(function ($query) {
                $query->whereDate('scheduled_date', '>', today())
                    ->orWhere(function ($innerQuery) {
                        $innerQuery->whereDate('scheduled_date', today())
                            ->where('start_time', '>=', now()->format('H:i'));
                    });
            })
            ->orderBy('scheduled_date')
            ->orderBy('start_time')
            ->limit(3)
            ->get();

        return view('home', compact('craftTypes', 'userBookings', 'sessionUser', 'upcomingMasterClasses'));
    }

    public function show(Request $request, CraftType $craftType): View
    {
        $sessionUser = $request->session()->get('user');
        $craftTypes = CraftType::orderBy('name')->get();
        $activeCraftType = $craftType;
        $masterClasses = $this->masterClassesQuery($craftType->id)->get();
        $userBookings = $this->userBookings($request);

        return view('category', compact('craftTypes', 'activeCraftType', 'masterClasses', 'userBookings', 'sessionUser'));
    }

    public function create(Request $request): View|RedirectResponse
    {

        $user = $this->getCurrentUser($request);

        if (! $user || ! $user->isMaster()) {
            return redirect('/')->withErrors(['auth' => 'Создание мастер-классов доступно только ведущему.']);
        }

        return view('form__master-class', [
            'craftTypes' => CraftType::orderBy('name')->get(),
            'masterClass' => new MasterClass,
            'timeSlots' => MasterClass::TIME_SLOTS,
            'isEdit' => false,
            'sessionUser' => $request->session()->get('user'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $this->getCurrentUser($request);

        if (! $user || ! $user->isMaster()) {
            return redirect('/')->withErrors(['auth' => 'Создание мастер-классов доступно только ведущему.']);
        }

        $validated = $this->validateMasterClass($request, $user);
        $user->masterClasses()->create($validated);

        return redirect()->route('cabinet')->with('status', 'Мастер-класс успешно добавлен.');
    }

    public function edit(Request $request, MasterClass $masterClass): View|RedirectResponse
    {
        $user = $this->getCurrentUser($request);

        if (! $user || ! $user->isMaster() || $masterClass->user_id !== $user->id) {
            return redirect('/')->withErrors(['auth' => 'Редактирование недоступно.']);
        }

        $masterClass->load('craftType');
        $masterClass->loadCount('participants');

        return view('form__master-class', [
            'craftTypes' => CraftType::orderBy('name')->get(),
            'masterClass' => $masterClass,
            'timeSlots' => MasterClass::TIME_SLOTS,
            'isEdit' => true,
            'participantsCount' => $masterClass->participants_count,
            'sessionUser' => $request->session()->get('user'),
        ]);
    }

    public function update(Request $request, MasterClass $masterClass): RedirectResponse
    {
        $user = $this->getCurrentUser($request);

        if (! $user || ! $user->isMaster() || $masterClass->user_id !== $user->id) {
            return redirect('/')->withErrors(['auth' => 'Редактирование недоступно.']);
        }

        $masterClass->loadCount('participants');

        $validated = $this->validateMasterClass($request, $user, $masterClass);

        $masterClass->update($validated);

        return redirect()->route('cabinet')->with('status', 'Мастер-класс обновлен.');
    }

    public function occupiedSlots(Request $request): JsonResponse
    {

        $user = $this->getCurrentUser($request);

        abort_unless($user && $user->isMaster(), 403);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'ignore_id' => ['nullable', 'integer'],
        ]);

        $query = $user->masterClasses()
            ->whereDate('scheduled_date', $validated['date'])
            ->when(
                ! empty($validated['ignore_id']),
                fn ($builder) => $builder->where('id', '!=', $validated['ignore_id'])
            );

        $slots = $query->pluck('start_time')
            ->values();

        return response()->json($slots);
    }

    private function validateMasterClass(Request $request, User $user, ?MasterClass $masterClass = null): array
    {
        $participantsCount = $masterClass->participants_count ?? 0;
        $hasParticipants = $participantsCount > 0;
        $currentPrice = $masterClass->price ?? null;

        $validated = $request->validate([
            'craft_type_id' => ['required', 'exists:craft_types,id'],
            'title' => [
                'required',
                'string',
                'max:'.MasterClass::TITLE_MAX_LENGTH,
                Rule::unique('master_classes', 'title')->ignore($masterClass?->id),
            ],
            'description' => ['required', 'string', 'max:'.MasterClass::DESCRIPTION_MAX_LENGTH],
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => [
                'required',
                Rule::in(array_keys(MasterClass::TIME_SLOTS)),
                Rule::unique('master_classes')->where(
                    fn ($query) => $query
                        ->where('user_id', $user->id)
                        ->where('scheduled_date', $request->input('scheduled_date'))
                )->ignore($masterClass?->id),
            ],
            'max_people' => ['required', 'integer', 'min:'.max(1, $participantsCount)],
            'price' => ['required', 'integer', 'min:1'],
        ], [
            'craft_type_id.required' => 'Выберите вид творчества.',
            'craft_type_id.exists' => 'Выбран неверный вид творчества.',
            'title.required' => 'Введите название мастер-класса.',
            'title.max' => 'Название не должно быть длиннее '.MasterClass::TITLE_MAX_LENGTH.' символов.',
            'title.unique' => 'Мастер-класс с таким названием уже существует.',
            'description.required' => 'Введите описание мастер-класса.',
            'description.max' => 'Описание не должно быть длиннее '.MasterClass::DESCRIPTION_MAX_LENGTH.' символов.',
            'scheduled_date.required' => 'Выберите дату.',
            'scheduled_date.date' => 'Укажите корректную дату.',
            'scheduled_date.after_or_equal' => 'Дата не может быть раньше сегодняшнего дня.',
            'start_time.required' => 'Выберите время.',
            'start_time.in' => 'Выберите время из доступных слотов.',
            'start_time.unique' => 'На эту дату и время у ведущего уже есть мастер-класс.',
            'max_people.required' => 'Введите количество человек в группе.',
            'max_people.integer' => 'Количество человек должно быть числом.',
            'max_people.min' => 'Количество человек должно быть больше нуля.',
            'price.required' => 'Введите стоимость.',
            'price.integer' => 'Стоимость должна быть числом.',
            'price.min' => 'Стоимость должна быть больше нуля.',
        ], [
            'craft_type_id' => 'вид творчества',
            'title' => 'название',
            'description' => 'описание',
            'scheduled_date' => 'дата',
            'start_time' => 'время',
            'max_people' => 'количество человек',
            'price' => 'стоимость',
        ]);

        $newDateTime = Carbon::parse($validated['scheduled_date'].' '.$validated['start_time']);

        if ($newDateTime->lt(now())) {
            throw ValidationException::withMessages([
                'start_time' => 'Нельзя выбрать дату и время, которые уже прошли.',
            ]);
        }

        if ($hasParticipants) {
            if ((int) $validated['craft_type_id'] !== (int) $masterClass->craft_type_id) {
                throw ValidationException::withMessages([
                    'craft_type_id' => 'Если на мастер-класс уже есть записи, вид творчества менять нельзя.',
                ]);
            }

            $originalDateTime = Carbon::parse($masterClass->scheduled_date->format('Y-m-d').' '.$masterClass->start_time);

            if ($newDateTime->lt($originalDateTime)) {
                throw ValidationException::withMessages([
                    'start_time' => 'Если на мастер-класс уже есть записи, переносить его можно только на это же время или позже.',
                ]);
            }

            if ($validated['start_time'] < $masterClass->start_time) {
                throw ValidationException::withMessages([
                    'start_time' => 'Если на мастер-класс уже есть записи, нельзя ставить время раньше уже указанного.',
                ]);
            }

            if ((int) $validated['price'] !== (int) $currentPrice) {
                throw ValidationException::withMessages([
                    'price' => 'После появления записавшихся участников стоимость менять нельзя.',
                ]);
            }
        }

        return $validated;
    }

    private function masterClassesQuery(int $craftTypeId)
    {
        return MasterClass::query()
            ->with(['master', 'craftType', 'participants'])
            ->forCraftType($craftTypeId)
            ->orderByRaw("CASE WHEN CONCAT(scheduled_date, ' ', start_time) < NOW() THEN 1 ELSE 0 END")
            ->orderByDesc('scheduled_date')
            ->orderByDesc('start_time');
    }

    private function userBookings(Request $request)
    {

        $user = $this->getCurrentUser($request);

        if (! $user || $user->isMaster()) {
            return collect();
        }

        return $user->bookedMasterClasses()
            ->with(['craftType', 'master'])
            ->orderBy('scheduled_date')
            ->orderBy('start_time')
            ->get();
    }
}
