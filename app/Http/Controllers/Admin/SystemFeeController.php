<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemFee;
use App\Models\DriverKpiConfig;
use Illuminate\Http\Request;

class SystemFeeController extends Controller
{
    public function edit()
    {
        $fee = SystemFee::current();
        $kpi = DriverKpiConfig::current();
        return view('admin.system_fees.edit', compact('fee', 'kpi'));
    }

    public function update(Request $request)
    {
        $messages = [
            'required' => 'Trường này không được để trống.',
            'integer'  => 'Phải là số nguyên.',
            'numeric'  => 'Phải là số.',
            'min'      => 'Giá trị nhỏ nhất là :min.',
            'max'      => 'Giá trị lớn nhất là :max.',
        ];

        // ── 1. Validate & lưu SystemFee ────────────────────────────────────
        $validatedFee = $request->validate([
            // Phí giao hàng cơ bản
            'standard_delivery_fee' => 'required|integer|min:0',
            'fast_delivery_fee'     => 'required|integer|min:0',
            'urgent_delivery_fee'   => 'required|integer|min:0',

            // Tỷ lệ chia doanh thu
            'driver_ratio'          => 'required|numeric|min:0|max:1',
            'platform_ratio'        => 'required|numeric|min:0|max:1',

            // Thuế & phí COD
            'driver_tax_percent'    => 'required|numeric|min:0|max:1',
            'cod_fee_percent'       => 'required|numeric|min:0|max:1',

            // Định giá theo khối lượng (matrix pricing)
            'base_weight'           => 'required|integer|min:1',
            'base_price'            => 'required|integer|min:0',
            'step_weight'           => 'required|integer|min:1',
            'step_price'            => 'required|integer|min:0',
            'zone_multiplier'       => 'required|numeric|min:0.1|max:10',
        ], $messages);

        $fee = SystemFee::current();
        $fee->update($validatedFee);

        // ── 2. Validate & lưu DriverKpiConfig ──────────────────────────────
        $validatedKpi = $request->validate([
            'pickup_reward'   => 'required|integer|min:0',
            'delivery_reward' => 'required|integer|min:0',
            'return_reward'   => 'required|integer|min:0',
        ], $messages);

        $kpi = DriverKpiConfig::current();
        $kpi->update($validatedKpi);

        return redirect()->back()->with('success', 'Đã lưu cấu hình phí hệ thống thành công!');
    }
}
