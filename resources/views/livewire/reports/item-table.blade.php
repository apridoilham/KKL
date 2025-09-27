<div class="overflow-x-auto">
    <table class="min-w-full text-sm text-left text-slate-500">
        <thead class="text-xs text-slate-700 uppercase bg-slate-50">
            <tr>
                <th scope="col" class="px-6 py-3">#</th>
                <th scope="col" class="px-6 py-3">Code</th>
                <th scope="col" class="px-6 py-3">Category</th>
                <th scope="col" class="px-6 py-3">Name</th>
                <th scope="col" class="px-6 py-3">Quantity</th>
                <th scope="col" class="px-6 py-3">Status</th>
                <th scope="col" class="px-6 py-3">Input At</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @foreach ($data as $index => $item)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4">{{ $index + 1 }}</td>
                    <td class="px-6 py-4 font-mono">{{ $item->code ?: '-' }}</td>
                    <td class="px-6 py-4">{{ $item->category ?: '-' }}</td>
                    <td class="px-6 py-4 font-semibold text-slate-900">{{ $item->name }}</td>
                    <td class="px-6 py-4 font-bold text-lg">{{ $item->quantity }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full {{ $item->status == 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ ucfirst($item->status) }}</span>
                    </td>
                    <td class="px-6 py-4">{{ $item->created_at->format('d M Y, H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>