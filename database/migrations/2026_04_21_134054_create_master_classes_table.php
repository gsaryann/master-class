<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('master_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('craft_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->date('scheduled_date');
            $table->string('start_time', 5);
            $table->unsignedInteger('max_people');
            $table->unsignedInteger('price');
            $table->timestamps();
        });

        $masterId = DB::table('users')->where('email', 'master@example.com')->value('id');
        $craftTypes = DB::table('craft_types')->pluck('id', 'name');

        $items = [
            [
                'craft_type_id' => $craftTypes['Архитектурное моделирование'] ?? null,
                'title' => 'Макет городского квартала',
                'description' => 'Участники соберут архитектурный макет и познакомятся с основами композиции.',
                'scheduled_date' => now()->addDays(1)->toDateString(),
                'start_time' => '09:00',
                'max_people' => 8,
                'price' => 900,
            ],
            [
                'craft_type_id' => $craftTypes['Кулинария'] ?? null,
                'title' => 'Декор капкейков',
                'description' => 'Практика по оформлению десертов и работе с базовыми кондитерскими материалами.',
                'scheduled_date' => now()->addDays(2)->toDateString(),
                'start_time' => '11:00',
                'max_people' => 10,
                'price' => 750,
            ],
            [
                'craft_type_id' => $craftTypes['Выпиливание лобзиком'] ?? null,
                'title' => 'Декоративная подставка из фанеры',
                'description' => 'Знакомство с шаблонами, резом лобзиком и аккуратной финишной обработкой изделия.',
                'scheduled_date' => now()->addDays(3)->toDateString(),
                'start_time' => '13:00',
                'max_people' => 6,
                'price' => 1100,
            ],
        ];

        foreach ($items as $item) {
            if (! $item['craft_type_id'] || ! $masterId) {
                continue;
            }

            DB::table('master_classes')->insert([
                ...$item,
                'user_id' => $masterId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_classes');
    }
};
