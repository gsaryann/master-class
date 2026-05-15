<?php

namespace Tests\Feature;

use App\Models\CraftType;
use App\Models\MasterClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_book_and_cancel_master_class(): void
    {
        $visitor = $this->createVisitor('booker@example.com', '+79005556677');
        $masterClass = MasterClass::query()->where('max_people', '>', 1)->firstOrFail();

        $this
            ->withSession($this->sessionFor($visitor))
            ->post(route('booking.store', $masterClass))
            ->assertRedirect(route('craft-types.show', $masterClass->craftType))
            ->assertSessionHas('status', 'Запись на мастер-класс подтверждена.');

        $this->assertDatabaseHas('registrations', [
            'user_id' => $visitor->id,
            'master_class_id' => $masterClass->id,
        ]);

        $this
            ->withSession($this->sessionFor($visitor))
            ->post(route('booking.cancel.submit', $masterClass), ['source' => 'category'])
            ->assertRedirect(route('craft-types.show', $masterClass->craftType))
            ->assertSessionHas('status', 'Запись на мастер-класс отменена.');

        $this->assertDatabaseMissing('registrations', [
            'user_id' => $visitor->id,
            'master_class_id' => $masterClass->id,
        ]);
    }

    public function test_booking_rejects_time_conflict_for_visitor(): void
    {
        $visitor = $this->createVisitor('conflict@example.com', '+79006667788');
        $master = User::query()->where('email', 'master@example.com')->firstOrFail();
        $craftType = CraftType::query()->firstOrFail();
        $date = now()->addDays(30)->toDateString();

        $first = MasterClass::create([
            'craft_type_id' => $craftType->id,
            'user_id' => $master->id,
            'title' => 'Первое занятие',
            'description' => 'Описание первого занятия',
            'scheduled_date' => $date,
            'start_time' => '09:00',
            'max_people' => 5,
            'price' => 1000,
        ]);

        $second = MasterClass::create([
            'craft_type_id' => $craftType->id,
            'user_id' => $master->id,
            'title' => 'Второе занятие',
            'description' => 'Описание второго занятия',
            'scheduled_date' => $date,
            'start_time' => '09:00',
            'max_people' => 5,
            'price' => 1000,
        ]);

        $visitor->bookedMasterClasses()->attach($first->id);

        $this
            ->withSession($this->sessionFor($visitor))
            ->post(route('booking.store', $second))
            ->assertRedirect(route('craft-types.show', $second->craftType))
            ->assertSessionHasErrors('booking');

        $this->assertDatabaseMissing('registrations', [
            'user_id' => $visitor->id,
            'master_class_id' => $second->id,
        ]);
    }

    private function createVisitor(string $email, string $phone): User
    {
        return User::create([
            'name' => 'Тестовый Посетитель',
            'email' => $email,
            'phone' => $phone,
            'role' => User::ROLE_VISITOR,
            'password' => Hash::make('secret123'),
        ]);
    }
}
