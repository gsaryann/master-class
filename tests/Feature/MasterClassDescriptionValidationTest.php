<?php

namespace Tests\Feature;

use App\Models\MasterClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterClassDescriptionValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_class_update_rejects_too_long_description(): void
    {
        $master = User::query()->where('email', 'master@example.com')->firstOrFail();
        $craftTypeId = \DB::table('craft_types')->value('id');

        $masterClass = MasterClass::create([
            'craft_type_id' => $craftTypeId,
            'user_id' => $master->id,
            'title' => 'Тестовый мастер-класс',
            'description' => 'Короткое описание',
            'scheduled_date' => '2026-05-10',
            'start_time' => '09:00',
            'max_people' => 8,
            'price' => 1200,
        ]);

        $response = $this
            ->withSession([
                'user' => [
                    'id' => $master->id,
                    'name' => $master->name,
                    'email' => $master->email,
                    'phone' => $master->phone,
                    'role' => $master->role,
                    'photo' => $master->photo,
                ],
            ])
            ->from(route('master-classes.edit', $masterClass))
            ->put(route('master-classes.update', $masterClass), [
                'craft_type_id' => $craftTypeId,
                'title' => 'Обновленный мастер-класс',
                'description' => str_repeat('а', MasterClass::DESCRIPTION_MAX_LENGTH + 1),
                'scheduled_date' => '2026-05-10',
                'start_time' => '09:00',
                'max_people' => 8,
                'price' => 1200,
            ]);

        $response
            ->assertRedirect(route('master-classes.edit', $masterClass))
            ->assertSessionHasErrors('description');

        $this->assertDatabaseHas('master_classes', [
            'id' => $masterClass->id,
            'description' => 'Короткое описание',
        ]);
    }
}
