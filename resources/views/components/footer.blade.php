{{--
    FOOTER COMPONENT (components/footer.blade.php)
    - Thông tin bản quyền
    - Thời gian server
--}}
<footer class="mt-auto py-3 px-4" style="background:#fff; border-top:1px solid #e2e8f0;">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span class="text-muted" style="font-size:0.82rem;">
            &copy; {{ date('Y') }} <strong>DeliRoute</strong> — Hệ thống quản lý lộ trình giao hàng.
        </span>
        <span class="text-muted" style="font-size:0.82rem;">
            <i class="bi bi-clock me-1"></i>
            Giờ máy chủ: {{ now()->format('H:i — d/m/Y') }}
        </span>
    </div>
</footer>
