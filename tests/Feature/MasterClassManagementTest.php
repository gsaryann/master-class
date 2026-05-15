<?php

namespace Tests\Feature;

use App\Models\CraftType;
use App\Models\MasterClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MasterClassManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_can_create_master_class(): void
    {
        $master = User::query()->where('email', 'master@example.com')->firstOrFail();
        $craftType = CraftType::query()->firstOrFail();
        $date = now()->addDays(40)->toDateString();

        $response = $this
            ->withSession($this->sessionFor($master))
            ->post(route('master-classes.store'), [
                'craft_type_id' => $craftType->id,
                'title' => 'Новый мастер-класс',
                'description' => 'Практическое занятие для проверки пайплайна.',
                'scheduled_date' => $date,
                'start_time' => '15:00',
                'max_people' => 7,
                'price' => 1500,
            ]);

        $response
            ->assertRedirect(route('cabinet'))
            ->assertSessionHas('status', 'Мастер-класс успешно добавлен.');

        $this->assertDatabaseHas('master_classes', [
            'title' => 'Новый мастер-класс',
            'user_id' => $master->id,
        ]);
    }

    public function test_visitor_cannot_open_master_class_creation_form(): void
    {
        $visitor = User::create([
            'name' => 'Тестовый Посетитель',
            'email' => 'visitor-create@example.com',
            'phone' => '+79007778899',
            'role' => User::ROLE_VISITOR,
            'password' => Hash::make('secret123'),
        ]);

        $this
            ->withSession($this->sessionFor($visitor))
            ->get(route('master-classes.create'))
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors('auth');
    }

    public function test_occupied_slots_returns_master_slots_for_selected_date(): void
    {
        $master = User::query()->where('email', 'master@example.com')->firstOrFail();
        $craftType = CraftType::query()->firstOrFail();
        $date = now()->addDays(45)->toDateString();

        MasterClass::create([
            'craft_type_id' => $craftType->id,
            'user_id' => $master->id,
            'title' => 'Занятый слот',
            'description' => 'Описание занятого слота.',
            'scheduled_date' => $date,
            'start_time' => '11:00',
            'max_people' => 6,
            'price' => 1200,
        ]);

        $this
            ->withSession($this->sessionFor($master))
            ->getJson(route('master-classes.occupied-slots', ['date' => $date]))
            ->assertOk()
            ->assertJson(['11:00']);
    }
}
