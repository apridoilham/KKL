@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between mt-6">
        <span>
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-500 bg-slate-800 border border-slate-700 cursor-default rounded-md">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <button wire:click="previousPage" wire:loading.attr="disabled" rel="prev" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-300 bg-slate-800 border border-slate-700 rounded-md hover:bg-slate-700">
                    {!! __('pagination.previous') !!}
                </button>
            @endif
        </span>

        <div class="hidden sm:flex sm:items-center">
            <p class="text-sm text-slate-400">
                Menampilkan
                <span class="font-medium text-white">{{ $paginator->firstItem() }}</span>
                sampai
                <span class="font-medium text-white">{{ $paginator->lastItem() }}</span>
                dari
                <span class="font-medium text-white">{{ $paginator->total() }}</span>
                hasil
            </p>
        </div>

        <span>
            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" wire:loading.attr="disabled" rel="next" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-300 bg-slate-800 border border-slate-700 rounded-md hover:bg-slate-700">
                    {!! __('pagination.next') !!}
                </button>
            @else
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-500 bg-slate-800 border border-slate-700 cursor-default rounded-md">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </span>
    </nav>
@endif