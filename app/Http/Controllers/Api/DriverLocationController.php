<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DriverLocationController extends Controller
{
    /**
     * Tài xế cập nhật vị trí hiện tại
     */
    public function updateLocation(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $taiXe = \Illuminate\Support\Facades\Auth::user()->taiXe;

        if (!$taiXe) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy thông tin tài xế'], 404);
        }

        $taiXe->update([
            'current_lat' => $request->lat,
            'current_lng' => $request->lng,
            'last_update' => now(),
        ]);

        // Tạo bản ghi lộ trình mới để hiển thị lịch sử trên admin
        \App\Models\LoTrinh::create([
            'tai_xe_id' => $taiXe->id,
            'latitude' => $request->lat,
            'longitude' => $request->lng,
            'thoi_gian' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Admin lấy danh sách vị trí tài xế
     */
    public function getLocations()
    {
        // Lấy tài xế online gần đây (có update trong 4 tiếng qua báo online, hoặc lấy hết, tuỳ yêu cầu)
        // User yêu cầu "fetch danh sách tài xế -> return id, name, lat, lng"
        // Ta cũng có thể return trạng thái để hiển thị.
        $taiXes = \App\Models\TaiXe::whereNotNull('current_lat')
            ->whereNotNull('current_lng')
            ->get();

        $data = $taiXes->map(function ($tx) {
            return [
                'id' => $tx->id,
                'name' => $tx->ho_ten,
                'lat' => $tx->current_lat,
                'lng' => $tx->current_lng,
                'status' => $tx->trang_thai,
                'plate' => $tx->bien_so_xe,
                'last_update' => $tx->last_update ? $tx->last_update->diffForHumans() : null,
            ];
        });

        return response()->json($data);
    }
}
