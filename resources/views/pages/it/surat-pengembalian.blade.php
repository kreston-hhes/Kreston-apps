@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Surat Pengembalian Aset" />

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
        activeTab: '{{ (request('asset_id') || $errors->any()) ? 'create' : 'history' }}',
        
        // Data dari Controller
        employees: {{ json_encode($employees ?? []) }},
        assets: {{ json_encode($assets ?? []) }},
        historyData: {{ json_encode($returns ?? []) }},
        
        // Form Data
        selectedEmployeeId: '{{ $autoOpenEmployeeId ?? '' }}',
        selectedAssetId: '{{ $autoOpenAsset->id ?? '' }}',
        keterangan_kembali: 'Switch Laptop',
        kondisi: {
            kebersihan: 'baik', fisik: 'baik', charger: 'baik',
            baterai: 'baik', tas: 'baik', lainnya: '-'
        },

        // Search & Filter
        searchQuery: '',
        dateFrom: '',
        dateTo: '',
        perPage: 5,
        currentPage: 1,

        init() {
            // Pantau perubahan aset! Jika bukan laptop, otomatis ganti alasan ke 'Dikembalikan'
            this.$watch('selectedAssetId', () => {
                if (!this.isLaptop && this.keterangan_kembali === 'Switch Laptop') {
                    this.keterangan_kembali = 'Dikembalikan';
                } else if (this.isLaptop && this.keterangan_kembali === 'Dikembalikan') {
                    this.keterangan_kembali = 'Switch Laptop';
                }
            });
        },

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
            
            // Pengecualian ketat
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
                    || (row.return_letter_number || '').toLowerCase().includes(q)
                    || nama.includes(q)
                    || (row.employee?.nik || '').toLowerCase().includes(q)
                    || (row.asset?.hostname || '').toLowerCase().includes(q)
                    || (row.asset?.name || '').toLowerCase().includes(q);

                const tanggal = row.returned_at ? row.returned_at.slice(0, 10) : '';
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
                    Riwayat Pengembalian
                    <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full bg-gray-100 text-gray-500" x-text="historyData.length"></span>
                </button>
                <button @click="activeTab = 'create'"
                    :class="activeTab === 'create' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                    class="pb-3 px-4 text-sm transition-colors">
                    + Buat Surat Pengembalian
                </button>
            </div>

            <!-- TAB 1: RIWAYAT PENGEMBALIAN -->
            <div x-show="activeTab === 'history'">
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
                                <th class="px-6 py-3 font-medium text-gray-500">Peminjam</th>
                                <th class="px-6 py-3 font-medium text-gray-500">Aset</th>
                                <th class="px-6 py-3 font-medium text-gray-500">Tgl Kembali</th>
                                <th class="px-6 py-3 font-medium text-gray-500">Fisik Terakhir</th>
                                <th class="px-6 py-3 font-medium text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-for="(row, index) in paginatedData" :key="index">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4 font-semibold text-blue-600" x-text="row.return_letter_number"></td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900 dark:text-white" x-text="row.employee?.first_name + ' ' + (row.employee?.last_name || '')"></div>
                                        <div class="text-xs text-gray-400" x-text="row.employee?.nik"></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900 dark:text-white" x-text="row.asset?.name"></div>
                                        <div class="text-xs font-mono text-gray-400" x-text="row.asset?.hostname"></div>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-700 dark:text-gray-300" x-text="row.returned_at"></td>
                                    <td class="px-6 py-4 capitalize" x-text="row.physical_condition || 'Baik'"></td>
                                    
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <a :href="`{{ route('handovers.return.pdf') }}?employee_id=${row.employee_id}&asset_id=${row.asset_id}&alasan=${row.assignment_reason}&kebersihan=${row.cleanliness_condition}&fisik=${row.physical_condition}&charger=${row.charger_condition}&baterai=${row.battery_condition}&tas=${row.bag_condition}&lainnya=${row.notes}`" target="_blank" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50 transition-colors">
                                                Preview
                                            </a>
                                            <button type="button"
                                                @click="if(confirm('Sembunyikan surat ini dari daftar?')) { historyData = historyData.filter(item => item.id !== row.id); }"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50 transition-colors">
                                                Hapus
                                            </button>
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

            <!-- TAB 2: PROSES PENGEMBALIAN -->
            <div x-show="activeTab === 'create'" x-cloak class="p-6">
                <form id="formPengembalian" :action="`/assets/surat-pengembalian/${selectedAsset?.current_assignment?.id || ''}`" method="POST"
                    @submit="if(!selectedAsset?.current_assignment?.id) { $event.preventDefault(); alert('Silakan pilih aset yang sedang dipinjam terlebih dahulu!'); }">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="employee_id" :value="selectedEmployeeId">
                    <input type="hidden" name="asset_id" :value="selectedAssetId">
                    
                    <div class="bg-gray-50 dark:bg-gray-900/50 p-6 rounded-xl border border-gray-100 dark:border-gray-700 mb-6">
                        
                        <!-- Karyawan & Aset -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Karyawan</label>
                                <select x-model="selectedEmployeeId" required
                                    x-init="let v = selectedEmployeeId; selectedEmployeeId = ''; $nextTick(() => selectedEmployeeId = v)"
                                    class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-white text-sm">
                                    <option value="">Pilih karyawan</option>
                                    <template x-for="emp in employees" :key="emp.id">
                                        <option :value="emp.id" x-text="emp.first_name + ' ' + (emp.last_name || '')"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Aset yang Dikembalikan</label>
                                <select x-model="selectedAssetId" required
                                    x-init="let v = selectedAssetId; selectedAssetId = ''; $nextTick(() => selectedAssetId = v)"
                                    class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-white text-sm">
                                    <option value="">Pilih aset...</option>
                                    <template x-for="asset in assets" :key="asset.id">
                                        <option :value="asset.id" x-text="(asset.hostname || asset.name) + ' | SN: ' + (asset.serial_number || '-')"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <!-- Alasan & Diwakilkan -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Alasan</label>
                                <select name="assignment_reason" x-model="keterangan_kembali" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-white text-sm">
                                    <template x-if="isLaptop">
                                        <option value="Switch Laptop">Switch Laptop</option>
                                    </template>
                                    <option value="Resign">Resign</option>
                                    <option value="Kerusakan Hardware">Kerusakan Hardware</option>
                                    <template x-if="!isLaptop">
                                        <option value="Dikembalikan">Dikembalikan</option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Apabila diwakilkan</label>
                                <select name="delegated_by_employee_id" class="w-full text-sm rounded-lg border-gray-300 dark:bg-gray-800 dark:text-white">
                                    <option value="">Pilih karyawan</option>
                                    <template x-for="emp in employees" :key="emp.id">
                                        <option :value="emp.id" x-text="emp.first_name + ' ' + (emp.last_name || '')"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <!-- Grid Kondisi Dasar (Selalu Tampil) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Kebersihan</label>
                                <select name="cleanliness_condition" x-model="kondisi.kebersihan" class="w-full text-sm rounded-lg border-gray-300 dark:bg-gray-800 dark:text-white"><option value="baik">Baik</option><option value="kurang">Kurang</option><option value="buruk">Buruk</option></select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Fisik <span x-text="isLaptop ? 'laptop' : 'aset'"></span></label>
                                <select name="physical_condition" x-model="kondisi.fisik" class="w-full text-sm rounded-lg border-gray-300 dark:bg-gray-800 dark:text-white"><option value="baik">Baik</option><option value="kurang">Kurang</option><option value="buruk">Buruk</option></select>
                            </div>
                        </div>

                        <!-- Grid Khusus Laptop (Hanya Tampil Jika Laptop) -->
                        <template x-if="isLaptop">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Charger</label>
                                    <select name="charger_condition" x-model="kondisi.charger" class="w-full text-sm rounded-lg border-gray-300 dark:bg-gray-800 dark:text-white"><option value="baik">Baik</option><option value="kurang">Kurang</option><option value="buruk">Buruk</option></select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Baterai</label>
                                    <select name="battery_condition" x-model="kondisi.baterai" class="w-full text-sm rounded-lg border-gray-300 dark:bg-gray-800 dark:text-white"><option value="baik">Baik</option><option value="kurang">Kurang</option><option value="buruk">Buruk</option><option value="tidak_ada">Tidak ada</option></select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Tas Laptop</label>
                                    <select name="bag_condition" x-model="kondisi.tas" class="w-full text-sm rounded-lg border-gray-300 dark:bg-gray-800 dark:text-white"><option value="baik">Baik</option><option value="kurang">Kurang</option><option value="buruk">Buruk</option><option value="tidak_ada">Tidak ada</option></select>
                                </div>
                            </div>
                        </template>

                        <!-- TAMBAHAN BARU: Hidden Input untuk Non-Laptop agar Laravel Tidak Error -->
                        <!-- Hidden Input untuk Non-Laptop agar Laravel & Database Tidak Error -->
                        <template x-if="!isLaptop">
                            <div>
                                <input type="hidden" name="charger_condition" value="baik">
                                <!-- UBAH value="tidak_ada" MENJADI value="baik" DI BAWAH INI -->
                                <input type="hidden" name="battery_condition" value="baik">
                                <input type="hidden" name="bag_condition" value="baik">
                            </div>
                        </template>
                        

                        <!-- Kondisi/Note dan Tanggal (Selalu Tampil, Lebar Penuh) -->
                        <div class="grid grid-cols-1 gap-4 mt-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Kondisi/note</label>
                                <input type="text" name="notes" x-model="kondisi.lainnya" placeholder="Isi catatan (opsional)" class="w-full text-sm rounded-lg border-gray-300 dark:bg-gray-800 dark:text-white">
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Pengembalian</label>
                                <x-form.date-picker name="returned_at" max="{{ date('Y-m-d') }}" altInput="true" altFormat="d-m-Y" class="bg-white dark:bg-gray-800 dark:border-gray-600 dark:text-white" />
                            </div>
                        </div>
                    </div>

                    <!-- AREA TOMBOL -->
                    <div class="flex items-center justify-end gap-3 mt-4">
                        <a :href="selectedEmployeeId && selectedAssetId
                                ? `{{ route('handovers.return.pdf') }}?` + new URLSearchParams({
                                    employee_id: selectedEmployeeId,
                                    asset_id: selectedAssetId,
                                    kebersihan: kondisi.kebersihan,
                                    fisik: kondisi.fisik,
                                    charger: kondisi.charger,
                                    baterai: kondisi.baterai,
                                    tas: kondisi.tas,
                                    lainnya: kondisi.lainnya,
                                    alasan: keterangan_kembali
                                }).toString()
                                : '#'"
                            @click="if (!selectedEmployeeId || !selectedAssetId) { $event.preventDefault(); alert('Pilih karyawan dan aset dulu sebelum preview.'); }"
                            target="_blank"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium rounded-lg dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Preview Surat
                        </a>

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
@endsection