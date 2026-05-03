{{-- admin/roles/index.blade.php --}}
@extends('layouts.admin')
@section('page_title', 'Phân quyền')

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show rounded-3 mb-4"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-heading">Phân quyền hệ thống</h1>
        <p class="page-subtext">Quản lý vai trò và quyền hạn của từng nhóm người dùng</p>
    </div>
    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary-custom">
        <i class="bi bi-plus-lg me-1"></i> Thêm vai trò
    </a>
</div>

<div class="data-table-wrapper">
    <div class="table-header">
        <h5><i class="bi bi-shield-lock me-2 text-primary"></i>Danh sách vai trò
            <span class="badge bg-primary ms-1">{{ $roles->count() }}</span>
        </h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tên vai trò</th>
                    <th>Mô tả</th>
                    <th>Số người dùng</th>
                    <th>Ngày tạo</th>
                    <th class="text-center">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @if(count($roles) > 0)
                @foreach($roles as $role)
                @php
                    $colors = [
                        '#fee2e2|#dc2626', '#fef3c7|#d97706', '#dbeafe|#1d4ed8',
                        '#f3e8ff|#7c3aed', '#dcfce7|#16a34a', '#f0fdf4|#15803d',
                    ];
                    [$bg, $fg] = explode('|', $colors[($role->id - 1) % count($colors)]);
                @endphp
                <tr>
                    <td class="text-muted">{{ $role->id }}</td>
                    <td>
                        <span class="status-badge" style="background:{{ $bg }}; color:{{ $fg }}; font-weight:700;">
                            {{ $role->name }}
                        </span>
                    </td>
                    <td style="font-size:.88rem; color:#475569;">{{ $role->mo_ta ?? '—' }}</td>
                    <td>
                        <span class="fw-bold">{{ $role->so_nguoi }}</span>
                        <span class="text-muted" style="font-size:.82rem;"> người</span>
                    </td>
                    <td style="font-size:.85rem; color:#94a3b8;">{{ \Carbon\Carbon::parse($role->created_at)->format('d/m/Y') }}</td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <a href="{{ route('admin.roles.edit', $role->id) }}"
                               class="btn btn-sm btn-outline-warning rounded-2" title="Sửa">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST"
                                  onsubmit="return confirm('Xoá vai trò {{ $role->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-2" title="Xoá">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-shield-x fs-2 d-block mb-2"></i>Không có vai trò nào.
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
