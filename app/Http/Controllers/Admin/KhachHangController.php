<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KhachHang;
use App\Models\XaPhuong;
use Illuminate\Http\Request;

class KhachHangController extends Controller
{
    public function index(Request $request)
    {
        $query = KhachHang::latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ten_khach', 'like', "%$search%")
                  ->orWhere('so_dien_thoai', 'like', "%$search%")
                  ->orWhere('dia_chi', 'like', "%$search%");
            });
        }

        $khachHangs = $query->paginate(15)->withQueryString();
        return view('admin.khach_hang.index', compact('khachHangs'));
    }

    public function create()
    {
        return view('admin.khach_hang.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ten_khach'         => 'required|string|max:100',
            'so_dien_thoai'     => 'required|string|max:20',
            'kh_tinh_id'        => 'nullable|string|exists:tinh_thanh,id',
            'kh_quan_id'        => 'nullable|string|exists:quan_huyen,id',
            'kh_xa_id'          => 'nullable|string|exists:xa_phuong,id',
            'kh_dia_chi_cu_the' => 'nullable|string|max:255',
            'latitude'          => 'nullable|numeric',
            'longitude'         => 'nullable|numeric',
        ], [
            'ten_khach.required'     => 'Vui lòng nhập tên khách hàng.',
            'so_dien_thoai.required' => 'Vui lòng nhập số điện thoại.',
        ]);

        // Xây địa chỉ đầy đủ + toạ độ từ Xã/Phường
        $addressData = $this->buildAddressData($validated);

        KhachHang::create(array_merge([
            'ten_khach'     => $validated['ten_khach'],
            'so_dien_thoai' => $validated['so_dien_thoai'],
        ], $addressData));

        return redirect()
            ->route('admin.khach_hang.index')
            ->with('success', 'Đã thêm khách hàng thành công!');
    }

    public function edit(KhachHang $khachHang)
    {
        return view('admin.khach_hang.edit', compact('khachHang'));
    }

    public function update(Request $request, KhachHang $khachHang)
    {
        $validated = $request->validate([
            'ten_khach'         => 'required|string|max:100',
            'so_dien_thoai'     => 'required|string|max:20',
            'kh_tinh_id'        => 'nullable|string|exists:tinh_thanh,id',
            'kh_quan_id'        => 'nullable|string|exists:quan_huyen,id',
            'kh_xa_id'          => 'nullable|string|exists:xa_phuong,id',
            'kh_dia_chi_cu_the' => 'nullable|string|max:255',
            'latitude'          => 'nullable|numeric',
            'longitude'         => 'nullable|numeric',
        ], [
            'ten_khach.required'     => 'Vui lòng nhập tên khách hàng.',
            'so_dien_thoai.required' => 'Vui lòng nhập số điện thoại.',
        ]);

        $addressData = $this->buildAddressData($validated);

        $khachHang->update(array_merge([
            'ten_khach'     => $validated['ten_khach'],
            'so_dien_thoai' => $validated['so_dien_thoai'],
        ], $addressData));

        return redirect()
            ->route('admin.khach_hang.index')
            ->with('success', 'Đã cập nhật khách hàng thành công!');
    }

    public function destroy(KhachHang $khachHang)
    {
        $khachHang->delete();

        return redirect()
            ->route('admin.khach_hang.index')
            ->with('success', 'Đã xoá khách hàng thành công!');
    }

    // ──────────────────────────────────────────────────────────────────────
    //  PRIVATE HELPERS
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Build cột dia_chi + lat/lng từ dropdown hành chính local DB.
     * Nếu user chọn Xã/Phường → tự lấy toạ độ GPS từ bảng xa_phuong.
     * Nếu user đã geocode thủ công → dùng lat/lng từ form (ưu tiên hơn).
     */
    private function buildAddressData(array $validated): array
    {
        $tinhId      = $validated['kh_tinh_id']        ?? null;
        $quanId      = $validated['kh_quan_id']        ?? null;
        $xaId        = $validated['kh_xa_id']          ?? null;
        $diaChiCuThe = $validated['kh_dia_chi_cu_the'] ?? '';
        $lat         = $validated['latitude']           ?? null;
        $lng         = $validated['longitude']          ?? null;

        $diaChiFull  = $diaChiCuThe;

        // Nếu có xã → lấy toạ độ GPS và tên đầy đủ từ DB
        if ($xaId) {
            $xa = XaPhuong::with(['quanHuyen.tinhThanh'])->find($xaId);
            if ($xa) {
                // Ưu tiên toạ độ user đã geocode, nếu không có thì lấy từ DB xa_phuong
                $lat = $lat ?: $xa->latitude;
                $lng = $lng ?: $xa->longitude;

                // Build chuỗi địa chỉ đầy đủ
                $parts = array_filter([
                    $diaChiCuThe,
                    $xa->name,
                    $xa->quanHuyen?->name,
                    $xa->quanHuyen?->tinhThanh?->name,
                ]);
                $diaChiFull = implode(', ', $parts);
            }
        }

        return [
            'tinh_thanh_id'  => $tinhId,
            'quan_huyen_id'  => $quanId,
            'xa_phuong_id'   => $xaId,
            'dia_chi_cu_the' => $diaChiCuThe,
            'dia_chi'        => $diaChiFull ?: '',
            'latitude'       => $lat,
            'longitude'      => $lng,
        ];
    }
}
