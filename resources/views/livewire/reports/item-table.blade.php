<div class="overflow-x-auto">
    <table class="min-w-full text-sm text-left text-slate-500">
        <thead class="text-xs text-slate-700 uppercase bg-slate-50">
            <tr>
                <th scope="col" class="px-6 py-3">No</th>
                <th scope="col" class="px-6 py-3">Kode</th>
                {{-- <th scope="col" class="px-6 py-3">Kategori</th> --}} {{-- Hapus Kategori --}}
                <th scope="col" class="px-6 py-3">Nama</th>
                <th scope="col" class="px-6 py-3 text-right">Harga Beli</th> {{-- Tambah Harga Beli --}}
                <th scope="col" class="px-6 py-3 text-right">Harga Jual</th> {{-- Tambah Harga Jual --}}
                <th scope="col" class="px-6 py-3 text-center">Kuantitas</th>
                <th scope="col" class="px-6 py-3">Status</th>
                <th scope="col" class="px-6 py-3">Tgl. Input</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse ($data as $index => $item)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4">{{ $loop->iteration }}</td>
                    <td class="px-6 py-4 font-mono text-slate-700">{{ $item->code ?: '-' }}</td>
                    {{-- <td class="px-6 py-4">{{ $item->category ?: '-' }}</td> --}} {{-- Hapus Kategori --}}
                    <td class="px-6 py-4 font-semibold text-slate-900">{{ $item->name }}</td>
                    {{-- Tambah Harga Beli & Jual --}}
                    <td class="px-6 py-4 text-right font-mono text-sm text-slate-600">
                        {{ $item->harga_beli ? 'Rp ' . number_format($item->harga_beli, 0, ',', '.') : '-' }}
                    </td>
                     <td class="px-6 py-4 text-right font-mono text-sm text-slate-600">
                        {{ $item->harga_jual ? 'Rp ' . number_format($item->harga_jual, 0, ',', '.') : '-' }}
                    </td>
                    {{-- Akhir Tambah Harga --}}
                    <td class="px-6 py-4 text-center font-bold text-lg text-slate-800">{{ $item->quantity }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $item->status == 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $item->status == 'available' ? 'Tersedia' : 'Habis' }}</span>
                    </td>
                    <td class="px-6 py-4">{{ $item->created_at->format('d M Y, H:i') }}</td>
                </tr>
            @empty
                <tr>
                    {{-- Sesuaikan colspan --}}
                    <td colspan="8" class="px-4 py-16 text-center">
                         <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" /></svg>
                         <h3 class="mt-2 text-lg font-semibold text-slate-800">Data Tidak Ditemukan</h3>
                         <p class="mt-1 text-sm text-slate-500">Tidak ada data yang cocok dengan kriteria filter Anda.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>