<?php

namespace Tests\Unit;

use App\Models\MasterClass;
use Carbon\Carbon;
use Tests\TestCase;

class MasterClassModelTest extends TestCase
{
    public function test_time_label_uses_configured_slot_label(): void
    {
        $masterClass = new MasterClass(['start_time' => '13:00']);

        $this->assertSame('13:00 - 15:00', $masterClass->time_label);
    }

    public function test_starts_at_combines_date_and_start_time(): void
    {
        $masterClass = new MasterClass([
            'scheduled_date' => Carbon::parse('2026-06-01'),
            'start_time' => '15:00',
        ]);

        $this->assertSame('2026-06-01 15:00', $masterClass->starts_at->format('Y-m-d H:i'));
    }
}
