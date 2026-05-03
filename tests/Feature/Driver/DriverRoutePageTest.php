<?php

namespace Tests\Feature\Driver;

use App\Models\DonHang;
use App\Models\Role;
use App\Models\RouteSession;
use App\Models\TaiXe;
use App\Models\TrangThaiDonHang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverRoutePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_route_page_uses_route_session_order_and_highlights_next_stop(): void
    {
        TrangThaiDonHang::factory()->create([
            'id' => TrangThaiDonHang::CHO_XU_LY,
            'ten_trang_thai' => 'Chờ xử lý',
        ]);

        TrangThaiDonHang::factory()->create([
            'id' => TrangThaiDonHang::DA_GIAO,
            'ten_trang_thai' => 'Đã giao',
        ]);

        $taiXeRole = Role::query()->create([
            'name' => 'TaiXe',
            'mo_ta' => 'Tài xế',
        ]);

        $taiXe = TaiXe::factory()->create();

        $user = User::factory()->create([
            'role_id' => $taiXeRole->id,
            'tai_xe_id' => $taiXe->id,
        ]);

        $doneOrder = DonHang::factory()->create([
            'tai_xe_id' => $taiXe->id,
            'trang_thai_id' => TrangThaiDonHang::DA_GIAO,
            'ma_don' => 'DH-DONE',
        ]);

        $nextOrder = DonHang::factory()->create([
            'tai_xe_id' => $taiXe->id,
            'trang_thai_id' => TrangThaiDonHang::CHO_XU_LY,
            'ma_don' => 'DH-NEXT',
            'cod_amount' => 120000,
        ]);

        RouteSession::factory()->create([
            'tai_xe_id' => $taiXe->id,
            'order_sequence' => [$doneOrder->id, $nextOrder->id],
            'total_orders' => 2,
            'completed_orders' => 1,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('driver.route.today'));

        $response->assertOk();
        $response->assertSee('Điểm tiếp theo');
        $response->assertSee('DH-NEXT');
        $response->assertSee('120.000');
    }
}
