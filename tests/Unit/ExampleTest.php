<?php

namespace Tests\Unit;

use App\Models\MasterClass;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_master_class_has_expected_time_slot_keys(): void
    {
        $this->assertSame(
            ['09:00', '11:00', '13:00', '15:00'],
            array_keys(MasterClass::TIME_SLOTS)
        );
    }
}
