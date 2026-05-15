<?php

namespace App\Http\Controllers;

use App\Models\CraftType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CabinetController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $this->getCurrentUser($request);

        if (! $user) {
            return redirect('/')->withErrors(['auth' => 'Для входа в личный кабинет нужно авторизоваться.']);
        }

        $craftTypes = CraftType::orderBy('name')->get();

        if ($user->isMaster()) {
            $user->load([
                'masterClasses' => fn ($query) => $query
                    ->with(['craftType', 'participants'])
                    ->orderByDesc('scheduled_date')
                    ->orderByDesc('start_time'),
            ]);

            return view('cabinet', [
                'user' => $user,
                'craftTypes' => $craftTypes,
                'isMasterCabinet' => true,
            ]);
        }

        $user->load([
            'bookedMasterClasses' => fn ($query) => $query
                ->with(['craftType', 'master'])
                ->orderBy('scheduled_date')
                ->orderBy('start_time'),
        ]);

        return view('cabinet', [
            'user' => $user,
            'craftTypes' => $craftTypes,
            'isMasterCabinet' => false,
        ]);
    }
}
