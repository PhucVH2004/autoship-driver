<?php

namespace App\Services;

use App\Models\SystemFee;

/**
 * PricingService
 *
 * Logic tính phí theo mô hình Logistics chuyên nghiệp (khối lượng quy đổi, bậc thang + loại hình giao).
 */
class PricingService
{
    /**
     * Tính trọng lượng tính cước (gram)
     */
    public function calculateChargeableWeight(int $weight, int $length = 0, int $width = 0, int $height = 0): int
    {
        $volumetricWeight = ($length * $width * $height) / 6000 * 1000;
        return (int) max($weight, $volumetricWeight);
    }

    /**
     * Tính phí giao hàng dựa vào trọng lượng tính cước, loại giao hàng và hệ số vùng
     */
    public function calculateShippingFee(int $chargeableWeight, string $deliveryType = 'standard', float $zoneMultiplier = 1.0): float
    {
        $feeConfig = SystemFee::current();

        // Base price phụ thuộc vào loại hình giao hàng
        $basePrice = match ($deliveryType) {
            'fast'   => $feeConfig->fast_delivery_fee,
            'urgent' => $feeConfig->urgent_delivery_fee,
            default  => $feeConfig->standard_delivery_fee, // Sử dụng giá standard thay vì base_price chung
        };

        $baseWeight = $feeConfig->base_weight;
        $stepWeight = $feeConfig->step_weight;
        $stepPrice  = $feeConfig->step_price;

        $shippingFee = $basePrice;

        if ($chargeableWeight > $baseWeight) {
            $excessWeight = $chargeableWeight - $baseWeight;
            $steps = ceil($excessWeight / $stepWeight);
            $shippingFee += ($steps * $stepPrice);
        }

        return (float) ($shippingFee * $zoneMultiplier);
    }

    /**
     * Tính phí COD
     */
    public function calculateCodFee(float $codAmount): float
    {
        $feeConfig = SystemFee::current();
        return (float) ($codAmount * $feeConfig->cod_fee_percent);
    }

    /**
     * Tính tổng phí toàn bộ cho đơn hàng
     */
    public function calculateAll(array $data): array
    {
        $weight = (int) ($data['weight'] ?? 0);
        $length = (int) ($data['length'] ?? 0);
        $width  = (int) ($data['width'] ?? 0);
        $height = (int) ($data['height'] ?? 0);

        $codAmount = (float) ($data['cod_amount'] ?? 0);
        $zoneMultiplier = (float) ($data['zone_multiplier'] ?? 1.0);
        $deliveryType = (string) ($data['delivery_type'] ?? 'standard');

        $chargeableWeight = $this->calculateChargeableWeight($weight, $length, $width, $height);
        $shippingFee = $this->calculateShippingFee($chargeableWeight, $deliveryType, $zoneMultiplier);
        $codFee = $this->calculateCodFee($codAmount);

        return [
            'chargeable_weight' => $chargeableWeight,
            'shipping_fee'      => $shippingFee,
            'cod_fee'           => $codFee,
            'total_fee'         => $shippingFee + $codFee,
        ];
    }
}
