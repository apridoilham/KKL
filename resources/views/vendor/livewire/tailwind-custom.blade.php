@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between mt-6">
        {{-- Tombol Previous --}}
        <span>
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-500 bg-slate-200 border border-slate-300 cursor-default rounded-md">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                {{-- PERBAIKAN FINAL: Menggunakan gotoPage untuk perintah yang lebih eksplisit --}}
                <button wire:click="gotoPage({{ $paginator->currentPage() - 1 }}, '{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" rel="prev" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-md hover:bg-slate-50">
                    {!! __('pagination.previous') !!}
                </button>
            @endif
        </span>

        {{-- Informasi Halaman --}}
        <div class="hidden sm:flex sm:items-center">
            @if ($paginator->total() > 0)
                <p class="text-sm text-slate-700">
                    Menampilkan
                    <span class="font-bold text-slate-900">{{ $paginator->firstItem() }}</span>
                    sampai
                    <span class="font-bold text-slate-900">{{ $paginator->lastItem() }}</span>
                    dari
                    <span class="font-bold text-slate-900">{{ $paginator->total() }}</span>
                    hasil
                </p>
            @endif
        </div>

        {{-- Tombol Next --}}
        <span>
            @if ($paginator->hasMorePages())
                {{-- PERBAIKAN FINAL: Menggunakan gotoPage untuk perintah yang lebih eksplisit --}}
                <button wire:click="gotoPage({{ $paginator->currentPage() + 1 }}, '{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" rel="next" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-md hover:bg-slate-50">
                    {!! __('pagination.next') !!}
                </button>
            @else
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-500 bg-slate-200 border border-slate-300 cursor-default rounded-md">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </span>
    </nav>
@endif