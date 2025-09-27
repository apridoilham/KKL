<div class="container-fluid px-4 md:px-6 py-6">

    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800">Inventory Items</h1>
            <p class="mt-1 text-slate-600">
                @if($items->total() > 0)
                    A complete list of all your inventory items. You currently have <span class="font-bold text-indigo-600">{{ $items->total() }}</span> distinct item(s).
                @else
                    Start managing your stock efficiently by adding your first inventory item.
                @endif
            </p>
        </div>
        <div class="flex items-center space-x-3 mt-4 md:mt-0 w-full md:w-auto">
            <div class="relative w-full md:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-slate-400"></i>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-lg leading-5 bg-white placeholder-slate-500 focus:outline-none focus:placeholder-slate-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all duration-200" placeholder="Search items...">
            </div>
            <button wire:click="create" class="inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i>
                Add Item
            </button>
        </div>
    </div>
    
    @if (session()->has('dataSession'))
        <div class="bg-{{ session('dataSession')->status == 'success' ? 'green' : 'red' }}-100 border-l-4 border-{{ session('dataSession')->status == 'success' ? 'green' : 'red' }}-500 text-{{ session('dataSession')->status == 'success' ? 'green' : 'red' }}-700 p-4 mb-6 rounded-md" role="alert">
            <p class="font-bold">{{ ucfirst(session('dataSession')->status) }}</p>
            <p>{{ session('dataSession')->message }}</p>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-slate-500">
                <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th scope="col" class="px-6 py-4">#</th>
                        <th scope="col" class="px-6 py-4">Code</th>
                        <th scope="col" class="px-6 py-4">Category</th>
                        <th scope="col" class="px-6 py-4">Name</th>
                        <th scope="col" class="px-6 py-4">Quantity</th>
                        <th scope="col" class="px-6 py-4">Status</th>
                        <th scope="col" class="px-6 py-4">Input At</th>
                        <th scope="col" class="px-6 py-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($items as $index => $item)
                        <tr class="hover:bg-slate-50 transition-colors duration-200">
                            <td class="px-6 py-4">{{ $items->firstItem() + $index }}</td>
                            <td class="px-6 py-4 font-mono text-slate-700">{{ $item->code ?: '-' }}</td>
                            <td class="px-6 py-4">{{ $item->category ?: '-' }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $item->name }}</td>
                            <td class="px-6 py-4 font-extrabold text-xl text-indigo-700">{{ $item->quantity }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full {{ $item->status == 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    <span class="w-2 h-2 mr-2 rounded-full {{ $item->status == 'available' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $item->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button wire:click="edit({{ $item->id }})" class="p-2 rounded-full text-blue-600 hover:bg-blue-100 transition-colors duration-200" title="Edit Data">
                                        <i class="fas fa-pen fa-sm"></i>
                                    </button>
                                    @if(auth()->user()->role == 'admin')
                                        <button wire:click="delete({{ $item->id }})" wire:confirm="Are you sure you want to delete this item?" class="p-2 rounded-full text-red-600 hover:bg-red-100 transition-colors duration-200" title="Delete Data">
                                            <i class="fas fa-trash fa-sm"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-16 px-4">
                                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                                </svg>
                                <h3 class="mt-2 text-lg font-semibold text-slate-800">No Items Found</h3>
                                <p class="mt-1 text-sm text-slate-500">Get started by adding your first inventory item.</p>
                                <div class="mt-6">
                                    <button wire:click="create" class="inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <i class="fas fa-plus mr-2"></i>
                                        Add Your First Item
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $items->links() }}
    </div>

    @if ($isModalOpen)
        <div 
            x-data="{ show: @entangle('isModalOpen') }" 
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            
            {{-- PERBAIKAN UTAMA: Mengganti bg-black dengan style yang lebih modern --}}
            class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4"
            x-cloak
        >
            <div 
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-90"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-90"
                @click.away="show = false"
                class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden"
            >
                <form wire:submit.prevent="store">
                    <div class="p-6 bg-indigo-600 text-white flex items-center justify-between">
                        <h3 class="text-xl font-bold flex items-center">
                            <i class="fas {{ $id ? 'fa-pencil-alt' : 'fa-plus-circle' }} mr-3"></i>
                            <span>{{ $id ? 'Edit Item' : 'Add New Item' }}</span>
                        </h3>
                        {{-- Perbaikan Tombol Close --}}
                        <button type="button" @click="show = false" class="text-indigo-200 hover:text-white text-3xl leading-none font-bold focus:outline-none">&times;</button>
                    </div>
                    
                    <div class="p-8 space-y-6">
                        <div>
                            <label for="code" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Item Code</label>
                            <input wire:model="code" type="text" id="code" class="mt-1 block w-full bg-transparent border-0 border-b-2 border-slate-200 p-0 pb-2 focus:ring-0 focus:border-indigo-500 transition duration-200" placeholder="e.g., BRG001">
                        </div>
                        <div>
                            <label for="category" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Category</label>
                            <input wire:model="category" type="text" id="category" class="mt-1 block w-full bg-transparent border-0 border-b-2 border-slate-200 p-0 pb-2 focus:ring-0 focus:border-indigo-500 transition duration-200" placeholder="e.g., Elektronik">
                        </div>
                        <div>
                            <label for="name" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Item Name <span class="text-red-500">*</span></label>
                            <input wire:model="name" type="text" id="name" class="mt-1 block w-full bg-transparent border-0 border-b-2 border-slate-200 p-0 pb-2 focus:ring-0 focus:border-indigo-500 transition duration-200" placeholder="e.g., Laptop" required>
                            @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="p-6 bg-slate-50 rounded-b-xl flex justify-end space-x-3 border-t border-slate-200">
                        <button type="button" @click="show = false" class="px-4 py-2.5 border border-slate-300 rounded-lg shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            Save Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>