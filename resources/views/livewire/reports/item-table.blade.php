<div class="overflow-x-auto">
    <table class="min-w-full text-sm text-left text-slate-500">
        <thead class="text-xs text-slate-700 uppercase bg-slate-50">
            <tr>
                <th scope="col" class="px-6 py-3">No</th>
                <th scope="col" class="px-6 py-3">Kode</th>
                <th scope="col" class="px-6 py-3">Kategori</th>
                <th scope="col" class="px-6 py-3">Nama</th>
                <th scope="col" class="px-6 py-3">Kuantitas</th>
                <th scope="col" class="px-6 py-3">Status</th>
                <th scope="col" class="px-6 py-3">Tgl. Input</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @foreach ($data as $index => $item)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4">{{ $loop->iteration }}</td>
                    <td class="px-6 py-4 font-mono text-slate-700">{{ $item->code ?: '-' }}</td>
                    <td class="px-6 py-4">{{ $item->category ?: '-' }}</td>
                    <td class="px-6 py-4 font-semibold text-slate-900">{{ $item->name }}</td>
                    <td class="px-6 py-4 font-bold text-lg text-slate-800">{{ $item->quantity }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $item->status == 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $item->status == 'available' ? 'Tersedia' : 'Habis' }}</span>
                    </td>
                    <td class="px-6 py-4">{{ $item->created_at->format('d M Y, H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>