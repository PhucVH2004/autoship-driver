@extends('layouts.shop')
@section('page_title', 'Danh sách đơn')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="fw-800 fs-4 mb-0">
        <i class="bi bi-box-seam" style="color:#6C63FF;"></i> Đơn hàng của Shop
    </h1>
    <a href="{{ route('shop.don_hang.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tạo đơn mới
    </a>
</div>

<div class="d-flex gap-2 mb-4 flex-wrap">
    <a href="{{ route('shop.don_hang.index', request()->except('trang_thai', 'page')) }}"
       class="btn btn-sm {{ !request('trang_thai') ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-4">
        Tất cả
        <span class="badge {{ !request('trang_thai') ? 'bg-white text-primary' : 'bg-secondary' }} ms-1 rounded-pill">
            {{ $counts['tat_ca'] ?? 0 }}
        </span>
    </a>
    @foreach($trangThais as $tt)
    <a href="{{ route('shop.don_hang.index', array_merge(request()->except('trang_thai', 'page'), ['trang_thai' => $tt->id])) }}"
       class="btn btn-sm {{ request('trang_thai') == $tt->id ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-4">
        {{ $tt->ten_trang_thai }}
        <span class="badge {{ request('trang_thai') == $tt->id ? 'bg-white text-primary' : 'bg-secondary' }} ms-1 rounded-pill">
            {{ $counts[$tt->id] ?? 0 }}
        </span>
    </a>
    @endforeach
</div>

<div class="bg-white rounded-4 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Mã đơn</th>
                    <th>Người nhận</th>
                    <th>Địa chỉ</th>
                    <th>COD</th>
                    <th>Phí ship</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th class="text-center">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($donHangs as $dh)
                <tr>
                    <td class="fw-600">{{ $dh->ma_don }}</td>
                    <td>
                        <div>{{ $dh->khachHang?->ten_khach }}</div>
                        <small class="text-muted">{{ $dh->khachHang?->so_dien_thoai }}</small>
                    </td>
                    <td class="text-muted small">{{ Str::limit($dh->khachHang?->dia_chi, 40) }}</td>
                    <td><strong>{{ number_format($dh->cod_amount, 0, ',', '.') }}đ</strong></td>
                    <td>{{ number_format($dh->shipping_fee, 0, ',', '.') }}đ</td>
                    <td>
                        <span class="badge rounded-pill" style="background:#EDE9FE;color:#6C63FF;font-size:.8rem;">
                            {{ $dh->trangThai?->ten_trang_thai ?? '—' }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ $dh->created_at->format('d/m/Y') }}</td>
                    <td>
                        <div class="d-flex gap-1 justify-content-center">
                            <a href="{{ route('shop.don_hang.show', $dh->id) }}"
                               class="btn btn-sm btn-outline-secondary" title="Xem chi tiết">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('shop.don_hang.edit', $dh->id) }}"
                               class="btn btn-sm btn-outline-warning" title="Sửa đơn hàng">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('shop.don_hang.destroy', $dh->id) }}" method="POST"
                                  onsubmit="return confirm('Bạn có chắc muốn xoá đơn hàng {{ $dh->ma_don }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Xoá đơn hàng">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                    Chưa có đơn hàng nào. <a href="{{ route('shop.don_hang.create') }}">Tạo đơn ngay →</a>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($donHangs instanceof \Illuminate\Pagination\LengthAwarePaginator && $donHangs->hasPages())
    <div class="px-4 py-3 border-top">
        {{ $donHangs->links() }}
    </div>
    @endif
</div>
@endsection
