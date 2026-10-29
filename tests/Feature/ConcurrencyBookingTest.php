<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ConcurrencyBookingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (\App\Models\Doctor::count() === 0) {
            \Illuminate\Support\Facades\Artisan::call('db:seed');
        }
    }

    /**
     * Test IV.3a: 20 yêu cầu đồng thời với Capacity = 1 (Tối đa 1 thành công, 19 từ chối, 0 overselling)
     */
    public function test_concurrent_booking_with_capacity_one(): void
    {
        $exitCode = Artisan::call('test:concurrency', [
            '--capacity' => 1,
            '--requests' => 20,
        ]);

        $output = Artisan::output();
        
        $this->assertEquals(0, $exitCode, "Kiểm thử đồng thời Capacity=1 thất bại:\n" . $output);
        $this->assertStringContainsString('KHÔNG XẢY RA (AN TOÀN)', $output);
        $this->assertStringContainsString('KIỂM THỬ ĐẠT (PASS)', $output);
    }

    /**
     * Test IV.3b: 20 yêu cầu đồng thời với Capacity = 5 (Tối đa 5 thành công, 15 từ chối, 0 overselling)
     */
    public function test_concurrent_booking_with_capacity_five(): void
    {
        $exitCode = Artisan::call('test:concurrency', [
            '--capacity' => 5,
            '--requests' => 20,
        ]);

        $output = Artisan::output();

        $this->assertEquals(0, $exitCode, "Kiểm thử đồng thời Capacity=5 thất bại:\n" . $output);
        $this->assertStringContainsString('KHÔNG XẢY RA (AN TOÀN)', $output);
        $this->assertStringContainsString('KIỂM THỬ ĐẠT (PASS)', $output);
    }
}
