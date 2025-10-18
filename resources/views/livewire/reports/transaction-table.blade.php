<div class="overflow-x-auto">
    <table class="min-w-full text-sm text-left text-slate-500">
        <thead class="text-xs text-slate-700 uppercase bg-slate-50">
            <tr>
                <th scope="col" class="px-6 py-3">No</th>
                <th scope="col" class="px-6 py-3">Nama Barang</th>
                <th scope="col" class="px-6 py-3">Tipe</th>
                <th scope="col" class="px-6 py-3">Kuantitas</th>
                <th scope="col" class="px-6 py-3">Detail Tambahan</th> {{-- Ganti Deskripsi --}}
                <th scope="col" class="px-6 py-3">Tanggal</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($data as $index => $transaction)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4">{{ $loop->iteration }}</td>
                    <td class="px-6 py-4">
                        <div class="font-semibold text-slate-900">{{ $transaction->item->name }}</div>
                        <div class="text-xs text-slate-500">{{ $transaction->item->code ?: '-' }}</div> {{-- Tampilkan Kode --}}
                    </td>
                    <td class="px-6 py-4">
                        @php
                            // Sesuaikan nama tipe transaksi
                            $types = [
                                'masuk_mentah' => ['text' => 'Masuk (Bahan Mentah)', 'class' => 'bg-green-100 text-green-800'],
                                'masuk_jadi' => ['text' => 'Masuk (Barang Jadi)', 'class' => 'bg-blue-100 text-blue-800'],
                                'produksi_jadi' => ['text' => 'Produksi (Barang Jadi)', 'class' => 'bg-sky-100 text-sky-800'],
                                'produksi_terpakai' => ['text' => 'Produksi (Terpakai)', 'class' => 'bg-orange-100 text-orange-800'],
                                'keluar_dikirim' => ['text' => 'Keluar (Kirim - Jadi)', 'class' => 'bg-yellow-100 text-yellow-800'],
                                'keluar_mentah' => ['text' => 'Keluar (Kirim - Mentah)', 'class' => 'bg-yellow-100 text-yellow-800'],
                                'rusak_mentah' => ['text' => 'Rusak (Bahan Mentah)', 'class' => 'bg-red-100 text-red-800'],
                                'rusak_jadi' => ['text' => 'Rusak (Barang Jadi)', 'class' => 'bg-red-100 text-red-800'],
                            ];
                            $typeInfo = $types[$transaction->type] ?? ['text' => ucfirst(str_replace('_', ' ', $transaction->type)), 'class' => 'bg-slate-100 text-slate-800'];
                        @endphp
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $typeInfo['class'] }}">
                            {{ $typeInfo['text'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 font-bold text-lg text-slate-800">{{ floatval($transaction->quantity) }}</td>
                    {{-- Ganti Deskripsi menjadi Detail Tambahan --}}
                    <td class="px-6 py-4 text-xs text-slate-500">
                         @if($transaction->nama_supplier)
                            <div>Supp: <span class="font-medium text-slate-700">{{ $transaction->nama_supplier }}</span></div>
                        @endif
                        @if($transaction->nama_customer)
                             <div>Cust: <span class="font-medium text-slate-700">{{ $transaction->nama_customer }}</span></div>
                        @endif
                        @if($transaction->nomor_surat_jalan)
                            <div>SJ: <span class="font-medium text-slate-700">{{ $transaction->nomor_surat_jalan }}</span></div>
                        @endif
                         @if($transaction->tanggal_surat_jalan)
                            {{-- Pastikan tanggal tidak null sebelum format --}}
                            <div>Tgl. SJ: <span class="font-medium text-slate-700">{{ optional($transaction->tanggal_surat_jalan)->format('d M Y') }}</span></div>
                        @endif
                         @if($transaction->description)
                            <div>Desc: <span class="font-medium text-slate-700">{{ Str::limit($transaction->description, 30) }}</span></div>
                        @endif
                        @if(!$transaction->nama_supplier && !$transaction->nama_customer && !$transaction->nomor_surat_jalan && !$transaction->description)
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4">{{ $transaction->created_at->format('d M Y, H:i') }}</td>
                </tr>
            @empty
                 <tr>
                    {{-- Sesuaikan colspan --}}
                    <td colspan="6" class="px-4 py-16 text-center">
                         <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                         <h3 class="mt-2 text-lg font-semibold text-slate-800">Data Tidak Ditemukan</h3>
                         <p class="mt-1 text-sm text-slate-500">Tidak ada data yang cocok dengan kriteria filter Anda.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>