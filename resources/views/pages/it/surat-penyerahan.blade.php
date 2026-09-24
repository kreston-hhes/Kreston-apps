@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Surat Penyerahan Aset" />

    @if (session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 text-green-700 border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 text-red-700 border border-red-200">
            <div class="font-bold text-sm mb-1">Gagal menyimpan data, mohon periksa kembali:</div>
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- WRAPPER ALPINE.JS -->
    <div class="w-full space-y-6 relative" x-data="{ 
        // Tab Management
        activeTab: '{{ (isset($autoOpenAsset) || $errors->any()) ? 'create' : 'history' }}', 
        
        // Data dari Controller
        employees: {{ json_encode($employees ?? []) }},
        assets: {{ json_encode($assets ?? []) }},
        historyData: {{ json_encode($handovers ?? []) }},
        
        // Form Data
        selectedEmployeeId: '',
        
        // Otomatis pilih ID aset
        selectedAssetId: {{ $autoOpenAsset ? $autoOpenAsset->id : "''" }},

        // Search & Filter (tabel Riwayat)
        searchQuery: '',
        dateFrom: '',
        dateTo: '',
        
        // Pagination
        perPage: 5,
        currentPage: 1,

        get selectedEmployee() {
            return this.employees.find(e => e.id == this.selectedEmployeeId) || null;
        },
        
        get selectedAsset() {
            return this.assets.find(a => a.id == this.selectedAssetId) || null;
        },

        get isLaptop() {
            if (!this.selectedAsset) return false; 
            
            const typeName = (this.selectedAsset.type?.name || '').toLowerCase();
            const catName = (this.selectedAsset.type?.category?.name || '').toLowerCase();
            
            if (typeName.includes('mouse') || typeName.includes('keyboard') || typeName.includes('flashdisk')) {
                return false;
            }
            
            return typeName.includes('laptop') 
                || typeName.includes('notebook') 
                || typeName === 'pc' 
                || catName === 'laptop';
        },

        get filteredData() {
            const q = this.searchQuery.trim().toLowerCase();
            return this.historyData.filter(row => {
                const nama = ((row.employee?.first_name || '') + ' ' + (row.employee?.last_name || '')).toLowerCase();
                const matchesSearch = !q
                    || (row.letter_number || '').toLowerCase().includes(q)
                    || nama.includes(q)
                    || (row.employee?.nik || '').toLowerCase().includes(q)
                    || (row.asset?.hostname || '').toLowerCase().includes(q)
                    || (row.asset?.name || '').toLowerCase().includes(q);

                const tanggal = row.assigned_at ? row.assigned_at.slice(0, 10) : '';
                const matchesFrom = !this.dateFrom || tanggal >= this.dateFrom;
                const matchesTo = !this.dateTo || tanggal <= this.dateTo;

                return matchesSearch && matchesFrom && matchesTo;
            });
        },

        get totalPages() {
            if (this.filteredData.length === 0) return 1;
            return Math.ceil(this.filteredData.length / this.perPage);
        },
        
        get paginatedData() {
            if (this.currentPage > this.totalPages) this.currentPage = this.totalPages;
            let start = (this.currentPage - 1) * this.perPage;
            return this.filteredData.slice(start, start + parseInt(this.perPage));
        },
        
        get visiblePages() {
            let c = this.currentPage, t = this.totalPages;
            if (t <= 5) return Array.from({ length: t }, (_, i) => i + 1);
            if (c <= 3) return [1, 2, 3, 4, '...', t];
            if (c >= t - 2) return [1, '...', t - 3, t - 2, t - 1, t];
            return [1, '...', c - 1, c, c + 1, '...', t];
        }
    }">

        <!-- MAIN CARD -->
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm dark:bg-gray-800 dark:border-gray-700">

            <!-- Tabs -->
            <div class="flex items-center gap-1 px-6 pt-5 border-b border-gray-100 dark:border-gray-700">
                <button @click="activeTab = 'history'; currentPage = 1"
                    :class="activeTab === 'history' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                    class="pb-3 px-4 text-sm transition-colors">
                    Riwayat Penyerahan
                    <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full bg-gray-100 text-gray-500" x-text="historyData.length"></span>
                </button>
                <button @click="activeTab = 'create'"
                    :class="activeTab === 'create' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                    class="pb-3 px-4 text-sm transition-colors">
                    + Buat Surat Penyerahan
                </button>
            </div>

            <!-- TAB 1: RIWAYAT PENYERAHAN -->
            <div x-show="activeTab === 'history'">
                <!-- Search & Filter -->
                <div class="flex flex-col md:flex-row md:items-end gap-3 px-6 pt-4 pb-2">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Cari</label>
                        <input type="text" x-model="searchQuery" @input="currentPage = 1"
                            placeholder="No surat, nama karyawan, NIK, atau hostname aset..."
                            class="w-full rounded-lg border-gray-300 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Dari tanggal</label>
                        <input type="date" x-model="dateFrom" @change="currentPage = 1"
                            class="rounded-lg border-gray-300 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Sampai tanggal</label>
                        <input type="date" x-model="dateTo" @change="currentPage = 1"
                            class="rounded-lg border-gray-300 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                    </div>
                    <button @click="searchQuery = ''; dateFrom = ''; dateTo = ''; currentPage = 1"
                        x-show="searchQuery || dateFrom || dateTo"
                        class="px-3 py-2 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg dark:border-gray-600 dark:hover:text-gray-300">
                        Reset filter
                    </button>
                </div>

                <div class="max-w-full overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-6 py-3 font-medium text-gray-500">No Surat</th>
                                <th class="px-6 py-3 font-medium text-gray-500">Penerima</th>
                                <th class="px-6 py-3 font-medium text-gray-500">Aset (Hostname)</th>
                                <th class="px-6 py-3 font-medium text-gray-500">Tanggal Serah Terima</th>
                                <!-- Tambahan Kolom Aksi -->
                                <th class="px-6 py-3 font-medium text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-for="(row, index) in paginatedData" :key="index">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4 font-semibold text-blue-600" x-text="row.letter_number"></td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900 dark:text-white" x-text="row.employee?.first_name + ' ' + (row.employee?.last_name || '')"></div>
                                        <div class="text-xs text-gray-400" x-text="row.employee?.nik"></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900 dark:text-white" x-text="row.asset?.name"></div>
                                        <div class="text-xs font-mono text-gray-400" x-text="row.asset?.hostname"></div>
                                    </td>
                                    <td class="px-6 py-4" x-text="row.assigned_at"></td>
                                    
                                    <!-- Tombol Preview PDF & Hapus -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <a :href="`{{ route('handovers.pdf') }}?employee_id=${row.employee_id}&asset_id=${row.asset_id}`" target="_blank" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                Preview
                                            </a>

                                            <!-- Hapus = soft delete, row tetap ada di DB, cuma ke-hide dari daftar ini -->
                                            <form :action="`/assets/surat-penyerahan/${row.id}`" method="POST"
                                                onsubmit="return confirm('Hapus surat ini dari daftar? (data tetap tersimpan di database, cuma disembunyikan dari tampilan)')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="flex flex-col items-center justify-between gap-4 px-6 py-4 border-t border-gray-100 sm:flex-row dark:border-gray-700">
                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                        <span>Show</span>
                        <select x-model.number="perPage" @change="currentPage = 1"
                            class="block px-3 py-1.5 text-sm border border-gray-200 rounded-lg bg-transparent text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="20">20</option>
                        </select>
                        <span>entries</span>
                    </div>
    
                    <div class="flex items-center gap-1 sm:gap-2" x-show="totalPages > 1" x-cloak>
                        <button @click="currentPage--" :disabled="currentPage === 1"
                            class="px-3 py-1.5 text-sm font-medium border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                            Prev
                        </button>
                        <div class="flex items-center gap-1">
                            <template x-for="page in visiblePages">
                                <button @click="if(page !== '...') currentPage = page"
                                    :class="{
                                        'bg-blue-600 text-white border-blue-600 shadow-md': currentPage === page,
                                        'text-gray-600 border-transparent hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.05]': currentPage !== page && page !== '...',
                                        'text-gray-400 cursor-default border-transparent': page === '...'
                                    }"
                                    class="px-3 py-1.5 text-sm font-medium border rounded-lg transition-colors" x-text="page" :disabled="page === '...'">
                                </button>
                            </template>
                        </div>
                        <button @click="currentPage++" :disabled="currentPage === totalPages"
                            class="px-3 py-1.5 text-sm font-medium border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                            Next
                        </button>
                    </div>
                </div>
            </div>

            <!-- TAB 2: BUAT SURAT BARU (Surat Penyerahan) -->
            <div x-show="activeTab === 'create'" x-cloak class="p-6">
                <form id="formPenyerahan" action="{{ route('handovers.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="employee_id" :value="selectedEmployeeId">
                    <input type="hidden" name="asset_id" :value="selectedAssetId">

                    <div class="bg-gray-50 dark:bg-gray-900/50 p-6 rounded-xl border border-gray-100 dark:border-gray-700 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Karyawan aktif</label>
                                <select x-model="selectedEmployeeId" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-white">
                                    <option value="">Pilih karyawan aktif</option>
                                    <template x-for="emp in employees" :key="emp.id">
                                        <option :value="emp.id" x-text="emp.first_name + ' ' + (emp.last_name || '') + ' - ' + emp.nik"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Aset tersedia</label>
                                <!-- TAMBAHKAN x-init UNTUK MEMANCING ALPINE.JS -->
                                <select x-model="selectedAssetId"
                                    x-init="let v = selectedAssetId; selectedAssetId = ''; $nextTick(() => selectedAssetId = v)"
                                    class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-white">
                                    <option value="">Pilih aset</option>
                                    <template x-for="asset in assets" :key="asset.id">
                                        <option :value="asset.id" x-text="(asset.hostname || asset.name) + ' | SN: ' + asset.serial_number"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <!-- Checkbox Kelengkapan & Tanggal -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center pt-2">
                            
                            <!-- 1. BUNGKUS DIV CHECKBOX DENGAN TEMPLATE INI -->
                            <template x-if="isLaptop">
                                <div class="flex items-center gap-6">
                                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                        <input type="checkbox" name="charger_included" value="1" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"> Charger
                                    </label>
                                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                        <input type="checkbox" name="bag_included" value="1" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"> Tas laptop
                                    </label>
                                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                        <input type="checkbox" name="battery_included" value="1" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"> Baterai
                                    </label>
                                </div>
                            </template>
                            
                            <!-- 2. TAMBAHKAN PENGGANJAL INI UNTUK NON-LAPTOP -->
                            <template x-if="!isLaptop">
                                <div></div>
                            </template>

                            <!-- TANGGAL PENYERAHAN BIARKAN UTUH -->
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Penyerahan</label>
                                <x-form.date-picker name="assigned_at" max="{{ date('Y-m-d') }}" altInput="true" altFormat="d-m-Y" class="bg-white dark:bg-gray-800 dark:border-gray-600 dark:text-white" />
                            </div>
                        </div>

                    <!-- AREA TOMBOL BARU -->
                    <div class="flex items-center justify-end gap-3 mt-4">
                        
                        <!-- Tombol 1: Preview (buka tab baru asli via <a>, bukan window.open() -->
                        <!-- window.open() lewat JS kadang malah nge-navigate tab yang sama di -->
                        <!-- browser/webview tertentu (terutama mobile), jadi form Alpine ke-reset -->
                        <!-- begitu balik. Pakai <a target="_blank"> lebih reliable & gak ganggu state. -->
                        <a :href="selectedEmployeeId && selectedAssetId
                                ? `{{ route('handovers.pdf') }}?employee_id=${selectedEmployeeId}&asset_id=${selectedAssetId}`
                                : '#'"
                            @click="if (!selectedEmployeeId || !selectedAssetId) { $event.preventDefault(); alert('Pilih karyawan dan aset dulu sebelum preview.'); }"
                            target="_blank"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium rounded-lg dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Preview Surat
                        </a>

                        <!-- Tombol 2: Simpan Utama (Submit permanen ke Laravel) -->
                        <button type="submit" 
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            Simpan Data
                        </button>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>
@endsection