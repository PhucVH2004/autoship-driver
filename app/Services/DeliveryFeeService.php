<?php

namespace App\Services;

/**
 * DeliveryFeeService
 *
 * Gom logic phân bổ doanh thu (tài xế, nền tảng, thuế) dựa trên cước phí đã tính.
 */
class DeliveryFeeService
{
    public function calculateDriverIncome(float $shippingFee): int
    {
        $fee = \App\Models\SystemFee::current();
        $shippingFee = max(0, $shippingFee);
        return (int) round($shippingFee * $fee->driver_ratio);
    }

    public function calculatePlatformFee(float $shippingFee): int
    {
        $fee = \App\Models\SystemFee::current();
        $shippingFee = max(0, $shippingFee);
        return (int) round($shippingFee * $fee->platform_ratio);
    }

    public function calculateTax(int $driverIncome): array
    {
        $fee = \App\Models\SystemFee::current();
        $driverIncome = max(0, $driverIncome);
        $driverTax = (int) round($driverIncome * $fee->driver_tax_percent);
        $driverRealIncome = (int) round($driverIncome - $driverTax);

        return [
            'driver_tax' => $driverTax,
            'driver_real_income' => $driverRealIncome,
        ];
    }

    /**
     * Trả về gói đầy đủ phân bổ dòng tiền (breakdown) dựa trên phí truyền vào
     */
    public function getBreakdown(float $shippingFee, float $codFee = 0): array
    {
        $driverIncome = $this->calculateDriverIncome($shippingFee);
        $platformFee = $this->calculatePlatformFee($shippingFee);
        $tax = $this->calculateTax($driverIncome);

        return [
            'delivery_fee' => $shippingFee,
            'platform_fee' => $platformFee,
            'driver_income' => $driverIncome,
            'driver_tax' => $tax['driver_tax'],
            'driver_real_income' => $tax['driver_real_income'],
            'cod_fee' => $codFee,
        ];
    }
}

