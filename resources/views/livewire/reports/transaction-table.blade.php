<div class="overflow-x-auto">
    <table class="min-w-full text-sm text-left text-slate-500">
        <thead class="text-xs text-slate-700 uppercase bg-slate-50">
            <tr>
                <th scope="col" class="px-6 py-3">No</th>
                <th scope="col" class="px-6 py-3">Nama Barang</th>
                <th scope="col" class="px-6 py-3">Tipe</th>
                <th scope="col" class="px-6 py-3">Kuantitas</th>
                <th scope="col" class="px-6 py-3">Deskripsi</th>
                <th scope="col" class="px-6 py-3">Tanggal</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach ($data as $index => $transaction)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4">{{ $loop->iteration }}</td>
                    <td class="px-6 py-4">
                        <div class="font-semibold text-slate-900">{{ $transaction->item->name }}</div>
                        <div class="text-xs text-slate-500">{{ $transaction->item->category }}</div>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $types = [
                                'masuk_mentah'    => ['text' => 'Masuk (Mentah)', 'class' => 'bg-green-100 text-green-800'],
                                'masuk_jadi'      => ['text' => 'Masuk (Jadi)', 'class' => 'bg-green-100 text-green-800'],
                                'keluar_terpakai' => ['text' => 'Keluar (Terpakai)', 'class' => 'bg-yellow-100 text-yellow-800'],
                                'keluar_dikirim'  => ['text' => 'Keluar (Dikirim)', 'class' => 'bg-yellow-100 text-yellow-800'],
                                'keluar_mentah'   => ['text' => 'Keluar (Mentah)', 'class' => 'bg-yellow-100 text-yellow-800'],
                                'rusak'           => ['text' => 'Rusak', 'class' => 'bg-red-100 text-red-800'],
                            ];
                            $typeInfo = $types[$transaction->type] ?? ['text' => 'Lainnya', 'class' => 'bg-slate-100 text-slate-800'];
                        @endphp
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $typeInfo['class'] }}">
                            {{ $typeInfo['text'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 font-bold text-lg text-slate-800">{{ floatval($transaction->quantity) }}</td>
                    <td class="px-6 py-4">{{ $transaction->description ?: '-' }}</td>
                    <td class="px-6 py-4">{{ $transaction->created_at->format('d M Y, H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>