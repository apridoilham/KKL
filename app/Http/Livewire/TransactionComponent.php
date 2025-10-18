<?php

namespace App\Http\Livewire;

use App\Models\Item;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth; // <-- Pastikan Auth di-import
use Illuminate\Support\Facades\Log; // <-- Import Log jika ingin debug

class TransactionComponent extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind-custom';
    public array $data;
    public string $search = '';
    public int $perPage = 10;
    public string $filterType = 'all';
    public ?int $id = null, $itemId = null;
    public ?string $type = null, $description = null;
    public int $quantity = 1;
    public $items = [];
    public bool $isModalOpen = false;

    public float $originalQuantity = 0;

    public ?string $nama_supplier = null;
    public ?string $nama_customer = null;
    public ?string $nomor_surat_jalan = null;
    public ?string $tanggal_surat_jalan = null;

    public string $filterDateType = 'all_time';
    public string $filterDate;
    public string $filterMonth;
    public string $filterYear;
    public string $filterSelectedMonth;
    public string $filterSelectedYear;

    public array $availableTypes = [];

    protected array $queryString = [
        'search' => ['except' => ''], 'perPage' => ['except' => 10], 'filterType' => ['except' => 'all'],
    ];

    public function mount(): void
    {
        $this->data = ['title' => 'Transaksi', 'urlPath' => 'transaction'];
        $this->resetDateFilters(false);
        $this->loadItems();
        $this->setAvailableTypes(); // Panggil method untuk set tipe
    }

    private function setAvailableTypes(): void
    {
        $user = Auth::user();
        // Jika user null (seharusnya tidak terjadi di middleware auth), beri array kosong
        if (!$user) {
            $this->availableTypes = [];
            return;
        }

        $allManualTypes = [
            'masuk_mentah' => 'Masuk (Bahan Mentah)',
            'masuk_jadi' => 'Masuk (Barang Jadi)',
            'keluar_mentah' => 'Keluar (Dikirim - Bahan Mentah)',
            'keluar_dikirim' => 'Keluar (Dikirim - Barang Jadi)',
            'rusak_mentah' => 'Rusak (Bahan Mentah)',
            'rusak_jadi' => 'Rusak (Barang Jadi)',
        ];

        // Debugging (hapus setelah selesai)
        // Log::info('User Role for Available Types: ' . $user->role);

        if ($user->role === 'pengiriman') {
            $this->availableTypes = [
                'keluar_mentah' => 'Keluar (Dikirim - Bahan Mentah)',
                'keluar_dikirim' => 'Keluar (Dikirim - Barang Jadi)',
            ];
        } elseif ($user->role === 'admin') {
             $this->availableTypes = $allManualTypes; // Admin bisa semua
        } elseif ($user->role === 'produksi') {
             // Contoh: produksi hanya boleh input rusak
             $this->availableTypes = [
                 'rusak_mentah' => 'Rusak (Bahan Mentah)',
                 'rusak_jadi' => 'Rusak (Barang Jadi)',
             ];
             // Jika produksi boleh semua seperti admin, uncomment baris di bawah dan comment blok di atasnya
             // $this->availableTypes = $allManualTypes;
        } else {
            // Role lain tidak bisa input manual
            $this->availableTypes = [];
        }

        // Debugging (hapus setelah selesai)
        // Log::info('Available Types Set: ' . json_encode($this->availableTypes));
    }


    public function resetDateFilters($resetPage = true): void
    {
        $this->filterDateType = 'all_time';
        $this->filterDate = now()->format('Y-m-d');
        $this->filterYear = now()->format('Y');
        $this->filterSelectedMonth = now()->format('m');
        $this->filterSelectedYear = now()->format('Y');
        $this->filterMonth = now()->format('Y-m');
        if ($resetPage) {
            $this->resetPage();
        }
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'filterType', 'filterDateType', 'filterDate', 'filterMonth', 'filterYear'])) {
            $this->resetPage();
        }
        if ($property === 'type') {
            $this->reset(['nama_supplier', 'nama_customer', 'nomor_surat_jalan', 'tanggal_surat_jalan']);
        }
    }

    private function updateFilterMonth(): void
    {
        $this->filterMonth = $this->filterSelectedYear . '-' . str_pad($this->filterSelectedMonth, 2, '0', STR_PAD_LEFT);
    }

    public function updatedFilterSelectedMonth(): void
    {
        $this->updateFilterMonth();
    }

    public function updatedFilterSelectedYear(): void
    {
        $this->updateFilterMonth();
    }


    public function updatedType($value): void
    {
        $this->loadItems($value);
        $this->reset(['nama_supplier', 'nama_customer', 'nomor_surat_jalan', 'tanggal_surat_jalan']);
    }

    public function loadItems($type = null): void
    {
        $query = Item::query();

        match ($type) {
            'masuk_mentah', 'keluar_mentah', 'rusak_mentah' => $query->where('item_type', 'barang_mentah'),
            'masuk_jadi', 'produksi_jadi', 'keluar_dikirim', 'rusak_jadi' => $query->where('item_type', 'barang_jadi'),
            default => null,
        };

        $this->items = $query->orderBy('name')->get();
    }

    public function resetInputFields(): void
    {
        $this->reset(['id', 'itemId', 'type', 'description', 'originalQuantity', 'nama_supplier', 'nama_customer', 'nomor_surat_jalan', 'tanggal_surat_jalan']);
        $this->quantity = 1;
        $this->loadItems();
    }

    public function create(): void
    {
        Gate::authorize('manage-transactions');
        $this->resetInputFields();
        $this->isModalOpen = true;
    }

    public function edit(int $id): void
    {
        Gate::authorize('edit-transactions'); // Hanya admin
        $transaction = Transaction::findOrFail($id);

        // Keamanan ganda: cek role lagi
        if (Auth::user()->role === 'pengiriman') {
             $this->dispatch('toast', status: 'failed', message: 'Anda tidak memiliki izin untuk mengedit transaksi.');
             return;
        }

        if (in_array($transaction->type, ['produksi_jadi', 'produksi_terpakai'])) {
            $this->dispatch('toast', status: 'failed', message: 'Transaksi produksi tidak dapat diedit.');
            return;
        }


        $this->id = $transaction->id;
        $this->itemId = $transaction->item_id;
        $this->type = $transaction->type;
        $this->quantity = $transaction->quantity;
        $this->description = $transaction->description;
        $this->originalQuantity = $transaction->quantity;
        $this->nama_supplier = $transaction->nama_supplier;
        $this->nama_customer = $transaction->nama_customer;
        $this->nomor_surat_jalan = $transaction->nomor_surat_jalan;
        $this->tanggal_surat_jalan = optional($transaction->tanggal_surat_jalan)->format('Y-m-d');

        $this->loadItems($transaction->type);
        $this->isModalOpen = true;
    }


    public function store(): void
    {
        if ($this->id) {
            Gate::authorize('edit-transactions'); // Hanya admin
             if(Auth::user()->role !== 'admin') { // Keamanan ganda
                 $this->dispatch('toast', status: 'failed', message: 'Anda tidak diizinkan mengubah transaksi.');
                 return;
             }
        } else {
            Gate::authorize('manage-transactions'); // Admin & Pengiriman bisa create
             if(Auth::user()->role === 'pengiriman' && !in_array($this->type, ['keluar_mentah', 'keluar_dikirim'])) {
                 $this->dispatch('toast', status: 'failed', message: 'Anda hanya dapat membuat transaksi keluar.');
                 return;
             }
        }

        $rules = [
            'itemId' => 'required|exists:items,id',
            'type' => ['required', Rule::in(array_keys($this->availableTypes))],
            'quantity' => 'required|numeric|min:1',
            'description' => 'nullable|string',
            'tanggal_surat_jalan' => 'nullable|date',
        ];

        if (in_array($this->type, ['masuk_mentah', 'masuk_jadi'])) {
            $rules['nama_supplier'] = 'required|string|max:255';
            $rules['nomor_surat_jalan'] = 'required|string|max:255';
            $rules['tanggal_surat_jalan'] = 'required|date';
        } elseif (in_array($this->type, ['keluar_dikirim', 'keluar_mentah'])) {
            $rules['nama_customer'] = 'required|string|max:255';
            $rules['nomor_surat_jalan'] = 'required|string|max:255';
            $rules['tanggal_surat_jalan'] = 'required|date';
        } else {
             $rules['nama_supplier'] = 'nullable|string|max:255';
             $rules['nama_customer'] = 'nullable|string|max:255';
             $rules['nomor_surat_jalan'] = 'nullable|string|max:255';
        }

        $validatedData = $this->validate($rules, [
            'quantity.required' => 'Kuantitas tidak boleh kosong.',
            'quantity.min' => 'Kuantitas minimal harus 1.',
            'itemId.required' => 'Anda harus memilih barang.',
            'type.in' => 'Tipe transaksi tidak valid untuk peran Anda.',
            'nama_supplier.required' => 'Nama supplier wajib diisi untuk barang masuk.',
            'nama_customer.required' => 'Nama customer wajib diisi untuk barang keluar.',
            'nomor_surat_jalan.required' => 'Nomor surat jalan wajib diisi.',
            'tanggal_surat_jalan.required' => 'Tanggal surat jalan wajib diisi.',
            'tanggal_surat_jalan.date' => 'Format tanggal surat jalan tidak valid.',
        ]);

        try {
            DB::transaction(function () use ($validatedData) {
                $stockInTypes = ['masuk_mentah', 'masuk_jadi'];

                $dataToSave = [
                    'item_id' => $validatedData['itemId'],
                    'type' => $validatedData['type'],
                    'quantity' => $validatedData['quantity'],
                    'description' => $validatedData['description'] ?? null,
                    'nama_supplier' => $validatedData['nama_supplier'] ?? null,
                    'nama_customer' => $validatedData['nama_customer'] ?? null,
                    'nomor_surat_jalan' => $validatedData['nomor_surat_jalan'] ?? null,
                    'tanggal_surat_jalan' => $validatedData['tanggal_surat_jalan'] ?? null,
                ];

                if ($this->id) {
                    $transaction = Transaction::findOrFail($this->id);
                    $oldItem = $transaction->item;
                    $oldStockInTypes = ['masuk_mentah', 'masuk_jadi', 'produksi_jadi'];
                    if (in_array($transaction->type, $oldStockInTypes)) {
                        $oldItem->decreaseStock($this->originalQuantity);
                    } else {
                        $oldItem->increaseStock($this->originalQuantity);
                    }

                    $newItem = Item::findOrFail($this->itemId);
                    if (in_array($this->type, $stockInTypes)) {
                        $newItem->increaseStock($this->quantity);
                    } else {
                        $newItem->decreaseStock($this->quantity);
                    }

                    $transaction->update($dataToSave);
                } else {
                    $item = Item::findOrFail($this->itemId);
                    if (in_array($this->type, $stockInTypes)) {
                        $item->increaseStock($this->quantity);
                    } else {
                        $item->decreaseStock($this->quantity);
                    }
                    Transaction::create($dataToSave);
                }
            });

            Cache::flush();
            $this->dispatch('toast', status: 'success', message: $this->id ? 'Transaksi berhasil diperbarui.' : 'Transaksi berhasil dibuat.');
            $this->isModalOpen = false;
            $this->resetInputFields();

        } catch (\Exception $e) {
            $this->dispatch('toast', status: 'failed', message: $e->getMessage());
        }
    }


    public function delete(int $id): void
    {
        Gate::authorize('manage-transactions'); // Admin & Pengiriman bisa delete (dgn batasan)
        $transaction = Transaction::findOrFail($id);

        if (in_array($transaction->type, ['produksi_jadi', 'produksi_terpakai'])) {
            $this->dispatch('toast', status: 'failed', message: 'Transaksi produksi tidak dapat dihapus manual.');
            return;
        }

        if (Auth::user()->role !== 'admin') {
             if (!in_array($transaction->type, ['keluar_mentah', 'keluar_dikirim'])) {
                 $this->dispatch('toast', status: 'failed', message: 'Anda hanya dapat menghapus transaksi keluar.');
                 return;
             }

            $lockTime = config('inventory.transaction_lock_time', 10);
            if (Carbon::parse($transaction->created_at)->diffInMinutes(Carbon::now()) > $lockTime) {
                $this->dispatch('toast', status: 'failed', message: "Transaksi terkunci setelah {$lockTime} menit.");
                return;
            }
        }

        try {
            DB::transaction(function () use ($transaction) {
                $item = $transaction->item;
                $stockInTypesToDelete = ['masuk_mentah', 'masuk_jadi', 'produksi_jadi'];
                if (in_array($transaction->type, $stockInTypesToDelete)) {
                    $item->decreaseStock($transaction->quantity);
                } else {
                    $item->increaseStock($transaction->quantity);
                }
                $transaction->delete();
            });

            Cache::flush();
            $this->dispatch('toast', status: 'success', message: 'Transaksi dihapus, stok dikembalikan.');
        } catch (\Exception $e) {
            $this->dispatch('toast', status: 'failed', message: $e->getMessage());
        }
    }


    public function render()
    {
        $transactionsQuery = Transaction::with('item')
            ->where(function ($query) {
                $query->whereHas('item', function ($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->search . '%');
                })->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhere('nama_supplier', 'like', '%' . $this->search . '%')
                  ->orWhere('nama_customer', 'like', '%' . $this->search . '%')
                  ->orWhere('nomor_surat_jalan', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterType !== 'all', function ($query) {
                $query->where('type', $this->filterType);
            });

        // Hapus filter berdasarkan role dari render()

        if ($this->filterDateType !== 'all_time') {
            switch ($this->filterDateType) {
                case 'daily':
                    $transactionsQuery->whereDate('created_at', $this->filterDate);
                    break;
                case 'monthly':
                    try {
                        $date = Carbon::parse($this->filterMonth);
                        $transactionsQuery->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month);
                    } catch (\Exception $e) {
                        //
                    }
                    break;
                case 'yearly':
                    $transactionsQuery->whereYear('created_at', $this->filterYear);
                    break;
            }
        }
        $transactions = $transactionsQuery->latest()->paginate($this->perPage);
        return view('livewire.transaction', ['transactions' => $transactions])->layout('components.layouts.app', ['data' => $this->data]);
    }
}