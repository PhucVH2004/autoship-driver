<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Schema;

class DonHang extends Model
{
    use HasFactory;

    private static ?bool $hasFinancialSummaryView = null;

    protected $table = 'don_hang';

    protected $fillable = [
        'ma_don',
        'tai_xe_id',
        'khach_hang_id',
        'trang_thai_id',
        'system_fees_id',
        'tong_tien',
        'delivery_type',
        'cod_amount',
        'delivery_fee',
        'ghi_chu',
        'thoi_gian_giao_du_kien',
        'thoi_gian_hoan_thanh',
        'delivery_photo',

        'weight',
        'length',
        'width',
        'height',
        'sender_id',
        'shipping_fee',
    ];

    protected $casts = [
        'tong_tien'               => 'float',
        'cod_amount'              => 'float',
        'delivery_fee'            => 'float',
        'shipping_fee'            => 'float',
        'weight'                  => 'integer',
        'length'                  => 'integer',
        'width'                   => 'integer',
        'height'                  => 'integer',
        'thoi_gian_giao_du_kien'  => 'datetime',
        'thoi_gian_hoan_thanh'    => 'datetime',
    ];

    /**
     * Financial relationship to the view.
     */
    public function financialSummary()
    {
        return $this->hasOne(DonHangFinancialSummary::class, 'id', 'id');
    }

    /** Accessors to maintain backward compatibility with dynamic calculation */
    public function getPlatformFeeAttribute()
    {
        if (array_key_exists('platform_fee', $this->attributes)) {
            return (float) ($this->attributes['platform_fee'] ?? 0);
        }

        if ($this->hasFinancialSummaryView()) {
            return (float) optional($this->financialSummary)->platform_fee;
        }

        return round((float) ($this->delivery_fee ?? 0) * $this->platformRatio(), 2);
    }

    public function getDriverIncomeAttribute()
    {
        if (array_key_exists('driver_income', $this->attributes)) {
            return (float) ($this->attributes['driver_income'] ?? 0);
        }

        if ($this->hasFinancialSummaryView()) {
            return (float) optional($this->financialSummary)->driver_income;
        }

        return round((float) ($this->delivery_fee ?? 0) * $this->driverRatio(), 2);
    }

    public function getDriverTaxAttribute()
    {
        if (array_key_exists('driver_tax', $this->attributes)) {
            return (float) ($this->attributes['driver_tax'] ?? 0);
        }

        if ($this->hasFinancialSummaryView()) {
            return (float) optional($this->financialSummary)->driver_tax;
        }

        return round((float) ($this->delivery_fee ?? 0) * $this->driverRatio() * $this->driverTaxPercent(), 2);
    }

    public function getDriverRealIncomeAttribute()
    {
        if (array_key_exists('driver_real_income', $this->attributes)) {
            return (float) ($this->attributes['driver_real_income'] ?? 0);
        }

        if ($this->hasFinancialSummaryView()) {
            return (float) optional($this->financialSummary)->driver_real_income;
        }

        return round(
            (float) ($this->delivery_fee ?? 0) * $this->driverRatio() * (1 - $this->driverTaxPercent()),
            2
        );
    }

    public function getCodFeeAttribute()
    {
        if (array_key_exists('cod_fee', $this->attributes)) {
            return (float) ($this->attributes['cod_fee'] ?? 0);
        }

        if ($this->hasFinancialSummaryView()) {
            return (float) optional($this->financialSummary)->cod_fee;
        }

        return round((float) ($this->cod_amount ?? 0) * $this->codFeePercent(), 2);
    }

    public function getTotalCollectionAttribute()
    {
        if (array_key_exists('total_collection', $this->attributes)) {
            return (float) ($this->attributes['total_collection'] ?? 0);
        }

        if ($this->hasFinancialSummaryView()) {
            return (float) optional($this->financialSummary)->total_collection;
        }

        return (float) (($this->cod_amount ?? 0) + ($this->shipping_fee ?? 0));
    }

    /** Tự sinh mã đơn: DH-001, DH-002... */
    public static function generateMaDon(int $id): string
    {
        return 'DH-' . str_pad($id, 3, '0', STR_PAD_LEFT);
    }

    // ──────────────────────────────────────
    // RELATIONSHIPS
    // ──────────────────────────────────────

    /** Đơn hàng thuộc về khách hàng nào */
    public function khachHang()
    {
        return $this->belongsTo(KhachHang::class, 'khach_hang_id');
    }

    /** Đơn hàng được giao bởi tài xế nào */
    public function taiXe()
    {
        return $this->belongsTo(TaiXe::class, 'tai_xe_id');
    }

    /** Trạng thái hiện tại của đơn hàng */
    public function trangThai()
    {
        return $this->belongsTo(TrangThaiDonHang::class, 'trang_thai_id');
    }

    /** Toàn bộ lịch sử thay đổi trạng thái của đơn hàng */
    public function lichSuTrangThais()
    {
        return $this->hasMany(LichSuTrangThai::class, 'don_hang_id')
                    ->orderBy('thoi_diem', 'desc');
    }

    /** Giao dịch ví liên quan đến đơn hàng */
    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'order_id');
    }

    /** Cấu hình phí hệ thống tại thời điểm tạo đơn */
    public function systemFee()
    {
        return $this->belongsTo(SystemFee::class, 'system_fees_id');
    }

    /** Shop gửi hàng (người gửi) */
    public function shop()
    {
        return $this->belongsTo(Shop::class, 'sender_id');
    }

    private function hasFinancialSummaryView(): bool
    {
        if (self::$hasFinancialSummaryView !== null) {
            return self::$hasFinancialSummaryView;
        }

        self::$hasFinancialSummaryView = Schema::hasTable('don_hang_financial_summary');

        return self::$hasFinancialSummaryView;
    }

    private function driverRatio(): float
    {
        return (float) optional($this->systemFee)->driver_ratio;
    }

    private function platformRatio(): float
    {
        return (float) optional($this->systemFee)->platform_ratio;
    }

    private function driverTaxPercent(): float
    {
        return (float) optional($this->systemFee)->driver_tax_percent;
    }

    private function codFeePercent(): float
    {
        return (float) optional($this->systemFee)->cod_fee_percent;
    }
}
