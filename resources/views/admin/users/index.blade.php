{{-- admin/users/index.blade.php --}}
@extends('layouts.admin')
@section('page_title', 'Người dùng hệ thống')

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show rounded-3 mb-4"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-heading">Người dùng hệ thống</h1>
        <p class="page-subtext">Quản lý tài khoản admin, dispatcher và tài xế</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary-custom">
        <i class="bi bi-plus-lg me-1"></i> Thêm người dùng
    </a>
</div>

{{-- Thanh tìm kiếm + lọc --}}
<form method="GET" action="{{ route('admin.users.index') }}" class="mb-4 d-flex gap-2 flex-wrap">
    <input type="text" name="search" class="form-control bg-light border-0 rounded-3"
           style="max-width:280px; font-size:.88rem;" placeholder="Tìm tên, email..."
           value="{{ request('search') }}">
    <select name="role_id" class="form-select bg-light border-0 rounded-3" style="max-width:180px; font-size:.88rem;">
        <option value="">— Tất cả vai trò —</option>
        @foreach($roles as $role)
        <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
            {{ $role->name }}
        </option>
        @endforeach
    </select>
    <button class="btn btn-primary rounded-3" type="submit"><i class="bi bi-search me-1"></i>Lọc</button>
    @if(request()->hasAny(['search','role_id']))
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary rounded-3"><i class="bi bi-x"></i></a>
    @endif
</form>

<div class="data-table-wrapper">
    <div class="table-header">
        <h5><i class="bi bi-person-gear me-2 text-primary"></i>Danh sách người dùng
            <span class="badge bg-primary ms-1">{{ $users->total() }}</span>
        </h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th class="text-center">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @if(count($users) > 0)
                @foreach($users as $user)
                @php
                    $roleColors = ['#fee2e2|#dc2626','#fef3c7|#d97706','#dbeafe|#1d4ed8','#f3e8ff|#7c3aed','#dcfce7|#16a34a'];
                    [$rbg,$rfg] = explode('|', $roleColors[($user->role_id - 1) % count($roleColors)]);
                @endphp
                <tr>
                    <td class="text-muted">{{ $user->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold"
                                 style="background:{{ $rbg }};color:{{ $rfg }};width:34px;height:34px;font-size:.8rem;flex-shrink:0;">
                                {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                            </div>
                            <strong>{{ $user->name }}</strong>
                        </div>
                    </td>
                    <td style="font-size:.85rem;">{{ $user->email }}</td>
                    <td>
                        <span class="badge" style="background:{{ $rbg }};color:{{ $rfg }};font-size:.8rem;padding:5px 10px;border-radius:8px;">
                            {{ $user->role_name ?? '—' }}
                        </span>
                    </td>
                    <td>
                        @if($user->trang_thai === 'Hoat dong')
                            <span class="status-badge status-done">Hoạt động</span>
                        @else
                            <span class="status-badge status-cancelled">Bị khoá</span>
                        @endif
                    </td>
                    <td style="font-size:.85rem; color:#94a3b8;">{{ $user->created_at->format('d/m/Y') }}</td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="btn btn-sm btn-outline-warning rounded-2" title="Sửa">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                  onsubmit="return confirm('Xoá tài khoản {{ $user->name }}?')">
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
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-person-x fs-2 d-block mb-2"></i>Không có người dùng nào.
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
    <div class="px-4 py-3" style="border-top:1px solid #f1f5f9;">{{ $users->links() }}</div>
    @endif
</div>
@endsection
