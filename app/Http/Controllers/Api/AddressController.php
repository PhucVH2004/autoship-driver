<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TinhThanh;
use App\Models\QuanHuyen;
use App\Models\XaPhuong;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * AddressController — API cho dropdown địa chỉ theo cấp hành chính (Sử dụng Database local)
 */
class AddressController extends Controller
{
    /** Danh sách tất cả Tỉnh/Thành */
    public function provinces(): JsonResponse
    {
        $provinces = TinhThanh::select('id', 'name')->orderBy('name')->get();
        return response()->json($provinces);
    }

    /** Quận/Huyện theo Tỉnh */
    public function districts(Request $request): JsonResponse
    {
        $request->validate(['tinh' => 'required|string']);

        $districts = QuanHuyen::where('tinh_thanh_id', $request->tinh)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($districts);
    }

    /** Xã/Phường theo Quận/Huyện */
    public function wards(Request $request): JsonResponse
    {
        $request->validate(['quan' => 'required|string']);

        $wards = XaPhuong::where('quan_huyen_id', $request->quan)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($wards);
    }

    /**
     * Geocode: Chuyển địa chỉ full thành toạ độ
     * POST /api/address/geocode
     * Body: { address: "123 Lê Lợi, Phường Bến Nghé, Quận 1, Hồ Chí Minh" }
     */
    public function geocode(Request $request): JsonResponse
    {
        $request->validate(['address' => 'required|string']);

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'User-Agent' => 'QuanLyGiaoHangApp/1.0'
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $request->address,
                'format' => 'json',
                'limit' => 1,
                'countrycodes' => 'vn'
            ]);

            if ($response->successful() && !empty($response->json())) {
                $result = $response->json()[0];
                return response()->json([
                    'lat' => (float) $result['lat'],
                    'lng' => (float) $result['lon'],
                ]);
            }
        } catch (\Exception $e) {
            // Lỗi kết nối API
        }

        return response()->json(['error' => 'Không tìm thấy toạ độ'], 404);
    }
}
