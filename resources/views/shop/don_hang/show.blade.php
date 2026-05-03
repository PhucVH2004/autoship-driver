@extends('layouts.shop')
@section('page_title', 'Chi tiết đơn hàng #' . $donHang->ma_don)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3">Đơn hàng #{{ $donHang->ma_don }}</h1>
            <small class="text-muted">Tạo lúc: {{ $donHang->created_at->format('d/m/Y H:i') }}</small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('shop.don_hang.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
            <a href="{{ route('shop.don_hang.edit', $donHang->id) }}" class="btn btn-outline-warning">
                <i class="bi bi-pencil-square"></i> Sửa
            </a>
            <form action="{{ route('shop.don_hang.destroy', $donHang->id) }}" method="POST"
                  onsubmit="return confirm('Bạn có chắc muốn xoá đơn hàng {{ $donHang->ma_don }}?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash"></i> Xoá
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-box"></i> Thông tin đơn hàng</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Mã đơn:</strong> {{ $donHang->ma_don }}</p>
                            <p><strong>Trạng thái:</strong>
                                <span class="badge rounded-pill" style="background:#EDE9FE;color:#6C63FF;">
                                    {{ $donHang->trangThai->ten_trang_thai ?? 'Không xác định' }}
                                </span>
                            </p>
                            <p><strong>Ghi chú:</strong> {{ $donHang->ghi_chu ?? 'Không có' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Khối lượng:</strong> {{ number_format($donHang->weight ?? 0) }}g</p>
                            <p><strong>Kích thước:</strong>
                                {{ $donHang->length ?? 0 }} × {{ $donHang->width ?? 0 }} × {{ $donHang->height ?? 0 }} cm
                            </p>
                            <p><strong>Tài xế phụ trách:</strong> {{ $donHang->taiXe->ho_ten ?? 'Chưa phân công' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person-check"></i> Thông tin người nhận</h5>
                </div>
                <div class="card-body">
                    <p><strong>Tên:</strong> {{ $donHang->khachHang->ten_khach }}</p>
                    <p><strong>SĐT:</strong> {{ $donHang->khachHang->so_dien_thoai }}</p>
                    <p><strong>Địa chỉ:</strong> {{ $donHang->khachHang->dia_chi }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-calculator"></i> Chi tiết phí</h5>
                </div>
                <div class="card-body">
                    @if($donHang->cod_amount > 0)
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span>Số tiền thu hộ (COD):</span>
                        <strong class="text-success">{{ number_format($donHang->cod_amount) }}đ</strong>
                    </div>
                    @endif

                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span>Phí vận chuyển:</span>
                        <span>{{ number_format($donHang->shipping_fee ?? 0) }}đ</span>
                    </div>

                    @if($donHang->cod_fee > 0)
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span>Phí thu hộ COD:</span>
                        <span class="text-danger">{{ number_format($donHang->cod_fee) }}đ</span>
                    </div>
                    @endif

                    <div class="d-flex justify-content-between py-3 border-top">
                        <strong>Tổng phí Shop phải trả:</strong>
                        <strong class="text-primary">{{ number_format(($donHang->shipping_fee ?? 0) + ($donHang->cod_fee ?? 0)) }}đ</strong>
                    </div>

                    @if($donHang->cod_amount > 0)
                    <div class="alert alert-info mt-3">
                        <small>
                            <strong>Shop thực nhận:</strong><br>
                            {{ number_format($donHang->cod_amount - ($donHang->shipping_fee ?? 0) - ($donHang->cod_fee ?? 0)) }}đ
                            <br>
                            <em>(= COD - Phí ship - Phí thu hộ)</em>
                        </small>
                    </div>
                    @endif
                </div>
            </div>

            @if($donHang->delivery_photo)
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-camera"></i> Ảnh xác nhận giao hàng</h6>
                </div>
                <div class="card-body text-center">
                    <img src="{{ asset('storage/' . $donHang->delivery_photo) }}"
                         class="img-fluid rounded"
                         alt="Ảnh giao hàng"
                         style="max-height: 200px; cursor: pointer;"
                         onclick="window.open(this.src, '_blank')">
                    <div class="mt-2">
                        <small class="text-muted">Click để xem kích thước đầy đủ</small>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    @if($donHang->lichSuTrangThais->isNotEmpty())
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Lịch sử trạng thái</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($donHang->lichSuTrangThais->sortByDesc('thoi_diem') as $lichSu)
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">{{ $lichSu->trangThai->ten_trang_thai ?? 'Không xác định' }}</h6>
                                <p class="text-muted mb-1">{{ $lichSu->thoi_diem->format('d/m/Y H:i:s') }}</p>
                                @if($lichSu->ghi_chu)
                                    <p class="mb-0"><small>{{ $lichSu->ghi_chu }}</small></p>
                                @endif
                                @if($lichSu->nguoiThayDoi)
                                    <p class="mb-0"><small class="text-muted">Bởi: {{ $lichSu->nguoiThayDoi->name }}</small></p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
    padding-left: 25px;
}

.timeline-marker {
    position: absolute;
    left: -33px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #007bff;
    border: 3px solid #fff;
    box-shadow: 0 0 0 3px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}
</style>
@endsection
