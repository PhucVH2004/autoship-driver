<?php

namespace Tests\Feature\Admin;

use App\Models\DonHang;
use App\Models\Role;
use App\Models\RouteSession;
use App\Models\TaiXe;
use App\Models\TrangThaiDonHang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRouteMonitoringPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_route_page_shows_route_summary_by_driver_from_route_session(): void
    {
        $this->seedRouteStatuses();

        $admin = $this->makeAdmin();
        $taiXe = TaiXe::factory()->create([
            'ho_ten' => 'Nguyễn Văn Route',
        ]);

        $pendingOrder = DonHang::factory()->create([
            'tai_xe_id' => $taiXe->id,
            'trang_thai_id' => TrangThaiDonHang::CHO_XU_LY,
            'ma_don' => 'DH-1001',
        ]);

        $doneOrder = DonHang::factory()->create([
            'tai_xe_id' => $taiXe->id,
            'trang_thai_id' => TrangThaiDonHang::DA_GIAO,
            'ma_don' => 'DH-1002',
        ]);

        RouteSession::factory()->create([
            'tai_xe_id' => $taiXe->id,
            'order_sequence' => [$pendingOrder->id, $doneOrder->id],
            'total_orders' => 2,
            'completed_orders' => 1,
            'total_km' => 12.5,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.lo_trinh.index'));

        $response->assertOk();
        $response->assertSee('Nguyễn Văn Route');
        $response->assertSee('DH-1001');
        $response->assertSee('DH-1002');
    }

    public function test_admin_can_move_pending_stop_up_within_route_session(): void
    {
        $this->seedRouteStatuses();

        $admin = $this->makeAdmin();
        $taiXe = TaiXe::factory()->create();

        $firstOrder = DonHang::factory()->create([
            'tai_xe_id' => $taiXe->id,
            'trang_thai_id' => TrangThaiDonHang::CHO_XU_LY,
            'ma_don' => 'DH-REORDER-1',
        ]);

        $secondOrder = DonHang::factory()->create([
            'tai_xe_id' => $taiXe->id,
            'trang_thai_id' => TrangThaiDonHang::CHO_XU_LY,
            'ma_don' => 'DH-REORDER-2',
        ]);

        $session = RouteSession::factory()->create([
            'tai_xe_id' => $taiXe->id,
            'order_sequence' => [$firstOrder->id, $secondOrder->id],
            'total_orders' => 2,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.lo_trinh.reorder-stop', $session), [
            'order_id' => $secondOrder->id,
            'direction' => 'up',
        ]);

        $response->assertRedirect(route('admin.lo_trinh.index', ['date' => $session->route_date->toDateString()]));
        $this->assertSame([$secondOrder->id, $firstOrder->id], $session->fresh()->orderedIds());
    }

    public function test_admin_can_reassign_active_order_to_another_driver(): void
    {
        $this->seedRouteStatuses();

        $admin = $this->makeAdmin();
        $fromDriver = TaiXe::factory()->create(['ho_ten' => 'Tài xế A']);
        $toDriver = TaiXe::factory()->create(['ho_ten' => 'Tài xế B']);

        $order = DonHang::factory()->create([
            'tai_xe_id' => $fromDriver->id,
            'trang_thai_id' => TrangThaiDonHang::DA_LAY_HANG,
            'ma_don' => 'DH-REALLOCATE-1',
        ]);

        $fromSession = RouteSession::factory()->create([
            'tai_xe_id' => $fromDriver->id,
            'order_sequence' => [$order->id],
            'total_orders' => 1,
            'status' => 'active',
        ]);

        RouteSession::factory()->create([
            'tai_xe_id' => $toDriver->id,
            'order_sequence' => [],
            'total_orders' => 0,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.lo_trinh.reassign-order'), [
            'order_id' => $order->id,
            'from_session_id' => $fromSession->id,
            'to_driver_id' => $toDriver->id,
        ]);

        $response->assertRedirect(route('admin.lo_trinh.index', ['date' => $fromSession->route_date->toDateString()]));
        $this->assertSame($toDriver->id, $order->fresh()->tai_xe_id);
        $this->assertSame([], $fromSession->fresh()->orderedIds());
        $this->assertSame([$order->id], RouteSession::query()->forDriver($toDriver->id)->forDate(today())->first()->orderedIds());
    }

    public function test_admin_cannot_reorder_completed_stop(): void
    {
        $this->seedRouteStatuses();

        $admin = $this->makeAdmin();
        $taiXe = TaiXe::factory()->create();

        $order = DonHang::factory()->create([
            'tai_xe_id' => $taiXe->id,
            'trang_thai_id' => TrangThaiDonHang::DA_GIAO,
        ]);

        $session = RouteSession::factory()->create([
            'tai_xe_id' => $taiXe->id,
            'order_sequence' => [$order->id],
            'total_orders' => 1,
            'status' => 'active',
        ]);

        $response = $this->from(route('admin.lo_trinh.index'))
            ->actingAs($admin)
            ->post(route('admin.lo_trinh.reorder-stop', $session), [
                'order_id' => $order->id,
                'direction' => 'up',
            ]);

        $response->assertRedirect(route('admin.lo_trinh.index'));
        $response->assertSessionHasErrors('order_id');
    }

    public function test_admin_route_page_shows_level_2_kpis_and_dispatch_labels(): void
    {
        $this->seedRouteStatuses();

        $admin = $this->makeAdmin();
        $taiXe = TaiXe::factory()->create([
            'ho_ten' => 'Tài xế Điều phối',
        ]);

        $order = DonHang::factory()->create([
            'tai_xe_id' => $taiXe->id,
            'trang_thai_id' => TrangThaiDonHang::CHO_XU_LY,
            'ma_don' => 'DH-KPI-01',
        ]);

        RouteSession::factory()->create([
            'tai_xe_id' => $taiXe->id,
            'order_sequence' => [$order->id],
            'total_orders' => 1,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.lo_trinh.index'));

        $response->assertOk();
        $response->assertSee('Tuyến đang chạy');
        $response->assertSee('Tài xế cần điều phối');
        $response->assertSee('Điều phối');
        $response->assertSee('Điểm tiếp theo');
        $response->assertSee('route-ops-kpi-strip');
        $response->assertSee('route-session-nav');
        $response->assertSee('route-session-detail');
        $response->assertSee('Màn hình điều hành hiện đại');
    }

    public function test_admin_route_page_renders_inline_dispatch_actions(): void
    {
        $this->seedRouteStatuses();

        $admin = $this->makeAdmin();
        $taiXe = TaiXe::factory()->create();

        $order = DonHang::factory()->create([
            'tai_xe_id' => $taiXe->id,
            'trang_thai_id' => TrangThaiDonHang::CHO_XU_LY,
            'ma_don' => 'DH-VIEW-01',
        ]);

        RouteSession::factory()->create([
            'tai_xe_id' => $taiXe->id,
            'order_sequence' => [$order->id],
            'total_orders' => 1,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.lo_trinh.index'));

        $response->assertOk();
        $response->assertSee('Lên');
        $response->assertSee('Xuống');
        $response->assertSee('Chuyển tài xế');
        $response->assertSee('route-detail-hero');
        $response->assertSee('route-stop-row');
    }

    public function test_admin_cannot_reassign_finished_order(): void
    {
        $this->seedRouteStatuses();

        $admin = $this->makeAdmin();
        $fromDriver = TaiXe::factory()->create();
        $toDriver = TaiXe::factory()->create();

        $order = DonHang::factory()->create([
            'tai_xe_id' => $fromDriver->id,
            'trang_thai_id' => TrangThaiDonHang::DA_GIAO,
        ]);

        $fromSession = RouteSession::factory()->create([
            'tai_xe_id' => $fromDriver->id,
            'order_sequence' => [$order->id],
            'total_orders' => 1,
            'status' => 'active',
        ]);

        $response = $this->from(route('admin.lo_trinh.index'))
            ->actingAs($admin)
            ->post(route('admin.lo_trinh.reassign-order'), [
                'order_id' => $order->id,
                'from_session_id' => $fromSession->id,
                'to_driver_id' => $toDriver->id,
            ]);

        $response->assertRedirect(route('admin.lo_trinh.index'));
        $response->assertSessionHasErrors('order_id');
    }

    public function test_admin_route_page_renders_master_detail_visual_shell_markers(): void
    {
        $this->seedRouteStatuses();

        $admin = $this->makeAdmin();
        $taiXe = TaiXe::factory()->create([
            'ho_ten' => 'Tài xế Master Detail',
            'bien_so_xe' => '51A-12345',
        ]);

        $order = DonHang::factory()->create([
            'tai_xe_id' => $taiXe->id,
            'trang_thai_id' => TrangThaiDonHang::CHO_XU_LY,
            'ma_don' => 'DH-MASTER-01',
        ]);

        RouteSession::factory()->create([
            'tai_xe_id' => $taiXe->id,
            'order_sequence' => [$order->id],
            'total_orders' => 1,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.lo_trinh.index'));

        $response->assertOk();
        $response->assertSee('route-ops-shell');
        $response->assertSee('route-ops-kpi-strip');
        $response->assertSee('route-session-nav');
        $response->assertSee('route-session-detail');
        $response->assertSee('Tài xế Master Detail');
        $response->assertSee('Điểm tiếp theo');
    }

    private function makeAdmin(): User
    {
        $adminRole = Role::query()->create([
            'name' => 'Admin',
            'mo_ta' => 'Quản trị viên',
        ]);

        return User::factory()->create([
            'role_id' => $adminRole->id,
        ]);
    }

    private function seedRouteStatuses(): void
    {
        TrangThaiDonHang::factory()->create([
            'id' => TrangThaiDonHang::CHO_XU_LY,
            'ten_trang_thai' => 'Chờ xử lý',
        ]);

        TrangThaiDonHang::factory()->create([
            'id' => TrangThaiDonHang::DA_LAY_HANG,
            'ten_trang_thai' => 'Đã lấy hàng',
        ]);

        TrangThaiDonHang::factory()->create([
            'id' => TrangThaiDonHang::DA_GIAO,
            'ten_trang_thai' => 'Đã giao',
        ]);

        TrangThaiDonHang::factory()->create([
            'id' => TrangThaiDonHang::HUY,
            'ten_trang_thai' => 'Hủy',
        ]);

        TrangThaiDonHang::factory()->create([
            'id' => TrangThaiDonHang::HOAN,
            'ten_trang_thai' => 'Hoàn',
        ]);

        TrangThaiDonHang::factory()->create([
            'id' => TrangThaiDonHang::DA_HOAN,
            'ten_trang_thai' => 'Đã hoàn',
        ]);
    }
}
