<div class="overflow-x-auto">
    <table class="min-w-full text-sm text-left text-slate-500">
        <thead class="text-xs text-slate-700 uppercase bg-slate-50">
            <tr>
                <th scope="col" class="px-6 py-3">#</th>
                <th scope="col" class="px-6 py-3">Item Name</th>
                <th scope="col" class="px-6 py-3">Type</th>
                <th scope="col" class="px-6 py-3">Quantity</th>
                <th scope="col" class="px-6 py-3">Description</th>
                <th scope="col" class="px-6 py-3">Date</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @foreach ($data as $index => $transaction)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4">{{ $index + 1 }}</td>
                    <td class="px-6 py-4">
                        <div class="font-semibold text-slate-900">{{ $transaction->item->name }}</div>
                        <div class="text-xs text-slate-500">{{ $transaction->item->category }}</div>
                    </td>
                    <td class="px-6 py-4">
                         @php
                            $typeClass = '';
                            if ($transaction->type == 'in') $typeClass = 'bg-green-100 text-green-800';
                            elseif ($transaction->type == 'out') $typeClass = 'bg-yellow-100 text-yellow-800';
                            else $typeClass = 'bg-red-100 text-red-800';
                        @endphp
                        <span class="px-2 py-1 text-xs rounded-full {{ $typeClass }}">{{ ucfirst($transaction->type) }}</span>
                    </td>
                    <td class="px-6 py-4 font-bold text-lg">{{ $transaction->quantity }}</td>
                    <td class="px-6 py-4">{{ $transaction->description ?: '-' }}</td>
                    <td class="px-6 py-4">{{ $transaction->created_at->format('d M Y, H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>