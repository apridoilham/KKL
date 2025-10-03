@section('title', $data['title'] ?? 'Cetak Laporan')
<div class="bg-white font-sans p-4 md:p-8">

    @if (session()->has('dataSession'))
        <div class="max-w-4xl mx-auto text-center p-8 border-2 border-dashed rounded-lg">
            <h2 class="text-xl font-bold text-red-600">Terjadi Kesalahan</h2>
            <p class="text-slate-600 mt-2">{{ session('dataSession')['message'] }}</p>
            <a href="/report" class="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg">Kembali ke Halaman Laporan</a>
        </div>
    @elseif ($reportData && $reportData->isNotEmpty())
        <div class="max-w-5xl mx-auto">
            <header class="text-center mb-8 pb-4 border-b">
                <h1 class="text-3xl font-bold text-slate-800">{{ $titleData }}</h1>
                <p class="text-slate-500">Dicetak pada: {{ now()->format('d F Y, H:i') }}</p>
            </header>

            <main>
                @if($filter == 'item')
                    @include('livewire.reports.item-table', ['data' => $reportData])
                @else
                    @include('livewire.reports.transaction-table', ['data' => $reportData])
                @endif
            </main>

            <footer class="mt-12 text-center text-sm text-slate-500">
                <p>Sistem Inventaris &copy; {{ date('Y') }}</p>
            </footer>
        </div>

        <script>
            // Script untuk otomatis memicu dialog print
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() { window.print(); }, 1000);
            });
        </script>
    @endif
</div>