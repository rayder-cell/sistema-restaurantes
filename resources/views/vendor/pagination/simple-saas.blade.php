@if ($paginator->hasPages())
    <nav class="flex items-center gap-1">
        {{-- Anterior --}}
        @if ($paginator->onFirstPage())
            <span class="saas-page-btn disabled">‹</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="saas-page-btn">‹</a>
        @endif

        {{-- Página actual --}}
        <span class="saas-page-btn active">{{ $paginator->currentPage() }}</span>

        {{-- Siguiente --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="saas-page-btn">›</a>
        @else
            <span class="saas-page-btn disabled">›</span>
        @endif
    </nav>
@endif

<style>
.saas-page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    font-size: 12px;
    border: 1px solid #e5e7eb;
    color: #6b7280;
    text-decoration: none;
    transition: all 0.15s;
}
.saas-page-btn:hover:not(.disabled):not(.active) {
    background: #f3f4f6;
}
.saas-page-btn.active {
    background: #6d28d9;
    border-color: #6d28d9;
    color: white;
    font-weight: 600;
}
.saas-page-btn.disabled {
    opacity: 0.4;
    cursor: default;
}
</style>
