@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Asset IT Management" />

    @if (session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400 border border-green-200 dark:border-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 p-4 rounded-xl bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400 border border-red-200 dark:border-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="w-full space-y-6 relative" x-data="{
        // Tab Management
        activeTab: '{{ request('tab', 'all') }}',
        isModalOpen: false,
        detailAset: null,
        
        // Data assets dari backend
        assetsData: (() => {
            let rawData = {{ json_encode($assets ?? []) }};
            return Array.isArray(rawData) ? rawData : (rawData.data || []);
        })(),
        get specObject() {
            const spec = this.detailAset?.specification;
            if (!spec) return {};
            return typeof spec === 'string' ? JSON.parse(spec) : spec;
        },
        
        perPage: 5,
        currentPage: 1,
        
        // Logic Filter Berdasarkan Tab
        get currentDataset() {
            if (this.activeTab === 'all') return this.assetsData;
            if (this.activeTab === 'gudang') return this.assetsData.filter(a => a.qc_status === 'pending');
            if (this.activeTab === 'server') return this.assetsData.filter(a => a.placement_status === 'it_room' || a.placement_status === 'server_room');
            if (this.activeTab === 'dipakai') return this.assetsData.filter(a => a.placement_status === 'used_by_employee');
            return this.assetsData;
        },
        
        // Pagination Logic
        get totalPages() {
            if (this.currentDataset.length === 0) return 1;
            return Math.ceil(this.currentDataset.length / this.perPage);
        },
        
        get paginatedData() {
            if (this.currentPage > this.totalPages) {
                this.currentPage = this.totalPages;
            }
            let start = (this.currentPage - 1) * this.perPage;
            let end = start + parseInt(this.perPage);
            return this.currentDataset.slice(start, end);
        },
        
        get visiblePages() {
            let c = this.currentPage;
            let t = this.totalPages;
            if (t <= 5) return Array.from({ length: t }, (_, i) => i + 1);
            if (c <= 3) return [1, 2, 3, 4, '...', t];
            if (c >= t - 2) return [1, '...', t - 3, t - 2, t - 1, t];
            return [1, '...', c - 1, c, c + 1, '...', t];
        },

        // Delete Action
        deleteRow(id, name) {
            if (!confirm(`Yakin ingin menghapus aset: ${name}?\nAksi ini tidak bisa dibatalkan.`)) return;
            fetch(`/assets/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        this.assetsData = this.assetsData.filter(r => r.id !== id);
                        window.showNotification?.({
                            variant: 'success',
                            title: 'Hapus Berhasil',
                            message: `${name} berhasil dihapus.`,
                        });
                    }
                })
                .catch(() => alert('Gagal menghapus data.'));
        },

        setQcStatus(id, status) {
            const label = status === 'passed' ? 'meluluskan' : 'menggagalkan';
            if (!confirm(`Yakin ingin ${label} QC aset ini?`)) return;
            fetch(`/it/assets/${id}/qc`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ qc_status: status }),
                })
                .then(r => { if (!r.ok) throw new Error(); return r; })
                .then(() => {
                    const row = this.assetsData.find(r => r.id === id);
                    if (row) row.qc_status = status;
                    window.showNotification?.({
                        variant: 'success',
                        title: 'QC Diperbarui',
                        message: status === 'passed' ? 'Aset dinyatakan lulus QC.' : 'Aset dinyatakan gagal QC.',
                    });
                })
                .catch(() => alert('Gagal memperbarui status QC.'));
        }
    }">

        {{-- MAIN CARD --}}
        <div class="bg-white border border-gray-200 rounded-2xl dark:bg-white/[0.03] dark:border-white/[0.05]">

            <!-- Tabs -->
            <div class="flex items-center gap-1 px-6 pt-5 border-b border-gray-100 dark:border-white/[0.05] overflow-x-auto">
                <button @click="activeTab = 'all'; currentPage = 1"
                    :class="activeTab === 'all' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                    class="pb-3 px-4 text-sm transition-colors whitespace-nowrap flex items-center">
                    Semua aset
                    <span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full transition-colors"
                        :class="activeTab === 'all' ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300' : 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-400'"
                        x-text="assetsData.length"></span>
                </button>
                
                <button @click="activeTab = 'gudang'; currentPage = 1"
                    :class="activeTab === 'gudang' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                    class="pb-3 px-4 text-sm transition-colors whitespace-nowrap flex items-center">
                    Aset baru / gudang
                    <span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full transition-colors"
                        :class="activeTab === 'gudang' ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300' : 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-400'"
                        x-text="assetsData.filter(a => a.qc_status === 'pending').length"></span>
                </button>
                
                <button @click="activeTab = 'server'; currentPage = 1"
                    :class="activeTab === 'server' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                    class="pb-3 px-4 text-sm transition-colors whitespace-nowrap flex items-center">
                    Aset di server room
                    <span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full transition-colors"
                        :class="activeTab === 'server' ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300' : 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-400'"
                        x-text="assetsData.filter(a => a.placement_status === 'it_room' || a.placement_status === 'server_room').length"></span>
                </button>
                
                <button @click="activeTab = 'dipakai'; currentPage = 1"
                    :class="activeTab === 'dipakai' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                    class="pb-3 px-4 text-sm transition-colors whitespace-nowrap flex items-center">
                    Aset dipakai karyawan
                    <span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full transition-colors"
                        :class="activeTab === 'dipakai' ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300' : 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-400'"
                        x-text="assetsData.filter(a => a.placement_status === 'used_by_employee').length"></span>
                </button>
            </div>

            <!-- Filter & Search -->
            <div class="p-6 border-b border-gray-100 dark:border-white/[0.05]">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <form action="{{ route('assets.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                        <div class="relative w-full sm:w-64">
                            <span class="absolute -translate-y-1/2 left-3 top-1/2 text-gray-400">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2" />
                                </svg>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Search Kode Aset or Nama..."
                                class="pl-10 dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        </div>

                        <!-- Dropdown Partnership -->
                        <select name="partnership" onchange="this.form.submit()"
                            class="px-3 py-2 border border-gray-200 rounded-lg text-sm dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 border-gray-300 bg-transparent text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">All Partners</option>
                            @foreach (\App\Models\Partnership::all() as $partner)
                                <option value="{{ $partner->id }}" {{ request('partnership') == $partner->id ? 'selected' : '' }}>
                                    {{ $partner->name }}
                                </option>
                            @endforeach
                        </select>

                        <!-- TAMBAHAN BARU: Dropdown Kategori -->
                        <select name="category" onchange="this.form.submit()"
                            class="px-3 py-2 border border-gray-200 rounded-lg text-sm dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 border-gray-300 bg-transparent text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">Semua Kategori</option>
                            @foreach (\App\Models\AssetType::all()->unique('name') as $type)
                                <option value="{{ $type->id }}" {{ request('category') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Tombol Clear diperbarui untuk mendeteksi filter category -->
                        @if (request()->anyFilled(['search', 'partnership', 'category']))
                            <a href="{{ route('assets.index') }}" class="text-sm text-red-500 hover:text-red-700">Clear</a>
                        @endif
                    </form>

                    <button @click="$dispatch('open-modal', 'modal-add-asset')"
                        class="inline-flex justify-center items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Aset Baru
                    </button>
                </div>
            </div>

            <!-- Tabel Data -->
            <div>
                <div class="max-w-full overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-start text-theme-xs font-medium text-gray-500 whitespace-nowrap">Kode Aset</th>
                                <th class="px-6 py-3 text-start text-theme-xs font-medium text-gray-500 whitespace-nowrap">Nama Perangkat</th>
                                
                                <th x-show="activeTab === 'dipakai'" x-cloak class="px-6 py-3 text-start text-theme-xs font-medium text-gray-500 whitespace-nowrap">Karyawan</th>
                                
                                <th class="px-6 py-3 text-start text-theme-xs font-medium text-gray-500 whitespace-nowrap">Lokasi / Partner</th>
                                <th class="px-6 py-3 text-start text-theme-xs font-medium text-gray-500 whitespace-nowrap">Kategori</th>
                                
                                <th x-show="activeTab === 'server'" x-cloak class="px-6 py-3 text-start text-theme-xs font-medium text-gray-500 whitespace-nowrap">Terakhir Dipakai</th>
                                <th x-show="activeTab === 'dipakai'" x-cloak class="px-6 py-3 text-start text-theme-xs font-medium text-gray-500 whitespace-nowrap">Tanggal Serah</th>
                                
                                <th x-show="activeTab !== 'dipakai'" x-cloak class="px-6 py-3 text-start text-theme-xs font-medium text-gray-500 whitespace-nowrap">Kondisi</th>
                                <th x-show="activeTab === 'all' || activeTab === 'gudang'" x-cloak class="px-6 py-3 text-start text-theme-xs font-medium text-gray-500 whitespace-nowrap">Status</th>
                                <th class="px-6 py-3 text-start text-theme-xs font-medium text-gray-500 whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in paginatedData" :key="row.id || index">
                                <tr class="border-b border-gray-100 dark:border-white/[0.05] hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                    
                                    <td class="px-6 py-3.5 font-mono font-semibold text-blue-600 dark:text-blue-400 whitespace-nowrap" x-text="row.asset_code"></td>
                                    
                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        <div class="font-medium text-gray-900 dark:text-white" x-text="row.name"></div>
                                        <div class="text-xs text-gray-400 font-mono" x-text="'SN: ' + (row.serial_number ?? '-')"></div>
                                    </td>

                                    <td x-show="activeTab === 'dipakai'" x-cloak class="px-6 py-3.5 text-theme-sm whitespace-nowrap">
                                        <span class="font-medium text-gray-900 dark:text-white" x-text="row.current_assignment?.employee?.first_name ?? 'Karyawan'"></span>
                                    </td>
                                    
                                    <td class="px-6 py-3.5 text-theme-sm whitespace-nowrap">
                                        <span class="px-2.5 py-1 text-xs font-medium rounded-md bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-900/20 dark:text-purple-400 dark:border-purple-800"
                                            x-text="row.partnership?.name ?? '-'"></span>
                                    </td>

                                    <td class="px-6 py-3.5 text-theme-sm whitespace-nowrap">
                                        <span class="font-medium text-gray-900 dark:text-gray-200" x-text="row.type?.name ?? '-'"></span>
                                    </td>
                                    
                                    <!-- KOLOM TERAKHIR DIPAKAI (DENGAN LOGIKA ALPINE JS BARU) -->
                                    <td x-show="activeTab === 'server'" x-cloak class="px-6 py-3.5 text-theme-sm whitespace-nowrap">
                                        <template x-if="row.assignments && row.assignments.length > 0 && row.assignments[0].employee">
                                            <!-- Tambahkan warna teks di sini -->
                                            <span class="text-gray-900 dark:text-gray-200 font-medium" x-text="row.assignments[0].employee.first_name"></span>
                                        </template>
                                        <template x-if="(!row.assignments || row.assignments.length === 0 || !row.assignments[0].employee) && row.latest_loan">
                                            <!-- Tambahkan warna teks di sini -->
                                            <span class="text-gray-900 dark:text-gray-200 font-medium">
                                                <span x-text="row.latest_loan.pic_name"></span>
                                                <span class="text-xs text-gray-500 font-medium ml-1">(Logbook)</span>
                                            </span>
                                        </template>
                                        <template x-if="(!row.assignments || row.assignments.length === 0 || !row.assignments[0].employee) && !row.latest_loan">
                                            <span class="text-gray-400">Belum pernah dipakai</span>
                                        </template>
                                    </td>

                                    <td x-show="activeTab === 'dipakai'" x-cloak class="px-6 py-3.5 text-theme-sm whitespace-nowrap">
                                        <!-- Tambahkan warna teks di sini -->
                                        <span class="text-gray-900 dark:text-gray-200" x-text="row.current_assignment?.assigned_at ? new Date(row.current_assignment.assigned_at).toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric'}) : '-'"></span>
                                    </td>
                                    
                                    <td x-show="activeTab !== 'dipakai'" x-cloak class="px-6 py-3.5 text-theme-sm font-medium whitespace-nowrap">
                                        <span x-show="row.condition_status === 'good'" class="text-green-600 dark:text-green-400">Bagus (Good)</span>
                                        <span x-show="row.condition_status === 'maintenance'" class="text-yellow-600 dark:text-yellow-400">Maintenance</span>
                                        <span x-show="row.condition_status === 'broken'" class="text-red-600 dark:text-red-400">Rusak</span>
                                    </td>

                                    <td x-show="activeTab === 'all' || activeTab === 'gudang'" x-cloak class="px-6 py-3.5 text-theme-sm whitespace-nowrap">
                                        <div x-show="row.placement_status === 'it_room' || row.placement_status === 'server_room'">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-gray-500"></span> Server Room
                                            </span>
                                            <!-- Sub-info: Nama peminjam terakhir -->
                                            <template x-if="row.assignments && row.assignments.length > 0">
                                                <p class="text-[11px] text-gray-400 mt-1" x-text="`Ex: ${row.assignments[0]?.employee?.first_name || '-'} ${row.assignments[0]?.employee?.last_name || ''}`"></p>
                                            </template>
                                            <template x-if="(!row.assignments || row.assignments.length === 0) && row.latest_loan">
                                                <p class="text-[11px] text-gray-400 mt-1" x-text="`Ex: ${row.latest_loan.pic_name} (Logbook)`"></p>
                                            </template>
                                        </div>
                                        <div x-show="row.placement_status === 'used_by_employee'">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400">
                                                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> Dipakai
                                            </span>
                                        </div>
                                        <div x-show="row.placement_status === 'gudang' || row.placement_status === 'new'">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">
                                                <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span> Gudang
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-3.5 whitespace-nowrap">
                                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                            <button @click="open = !open"
                                                class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-white/[0.05] transition-colors">
                                                <svg class="size-5" fill="currentColor" viewBox="0 0 24 24">
                                                    <circle cx="12" cy="5" r="1.5" />
                                                    <circle cx="12" cy="12" r="1.5" />
                                                    <circle cx="12" cy="19" r="1.5" />
                                                </svg>
                                            </button>
                                            
                                        <div x-show="open" x-cloak x-transition
                                                class="absolute right-0 z-30 mt-1 w-52 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-white/[0.08] py-1">
                                                
                                                <button @click="detailAset = row; isModalOpen = true; open = false" class="flex w-full items-center gap-2.5 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/[0.05] transition-colors">
                                                    <svg class="size-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg> Lihat Detail
                                                </button>

                                                <button x-show="row.placement_status === 'gudang' || row.placement_status === 'new' || row.placement_status === 'it_room' || row.placement_status === 'server_room'" 
                                                        @click="window.location.href = (row.type?.document_flow === 'logbook' || (row.type?.name || '').toLowerCase().includes('proyektor')) ? `/assets/logbook?asset_id=${row.id}` : `/assets/surat-penyerahan?asset_id=${row.id}&auto_open=1`" 
                                                        class="flex w-full items-center gap-2.5 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/[0.05] transition-colors">
                                                    <svg class="size-4 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                    </svg> 
                                                    <span x-text="(row.type?.document_flow === 'logbook' || (row.type?.name || '').toLowerCase().includes('proyektor')) ? 'Catat di Logbook' : 'Serahkan ke Karyawan'"></span>
                                                </button>
                                                <button x-show="row.placement_status === 'used_by_employee'" 
                                                        @click="window.location.href = `/assets/surat-pengembalian?asset_id=${row.id}&auto_open=1`" 
                                                        class="flex w-full items-center gap-2.5 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/[0.05] transition-colors">
                                                    <svg class="size-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 17l-4-4m4 4l4-4m-4 4V3m-8 14H4a2 2 0 01-2-2V5a2 2 0 012-2h4" />
                                                    </svg> Kembalikan
                                                </button>

                                                <button @click="window.location.href = `/assets/${row.id}/edit`" class="flex w-full items-center gap-2.5 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/[0.05] transition-colors">
                                                    <svg class="size-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg> Edit Data
                                                </button>

                                                <div class="my-1 border-t border-gray-100 dark:border-white/[0.06]"></div>
                                                
                                                <button @click="deleteRow(row.id, row.name); open = false" class="flex w-full items-center gap-2.5 px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg> Hapus Aset
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            
                            <template x-if="currentDataset.length === 0">
                                <tr>
                                    <td :colspan="activeTab === 'dipakai' ? 7 : (activeTab === 'server' ? 8 : 8)" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-400 dark:text-gray-500">
                                            <svg class="w-12 h-12 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                            </svg>
                                            <p class="text-sm font-medium">Tidak ada aset pada tab ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="flex flex-col items-center justify-between gap-4 px-6 py-4 border-t border-gray-100 sm:flex-row dark:border-white/[0.05]">
                <div class="flex items-center gap-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Show</p>
                    <select x-model.number="perPage" @change="currentPage = 1"
                        class="block px-3 py-1.5 text-sm border border-gray-200 rounded-lg bg-transparent text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="25">25</option>
                    </select>
                    <p class="text-sm text-gray-500 dark:text-gray-400">entries</p>
                </div>

                <div class="flex items-center gap-1 sm:gap-2" x-show="totalPages > 1" x-cloak>
                    <button @click="currentPage--" :disabled="currentPage === 1"
                        class="px-3 py-1.5 text-sm font-medium border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                        Prev
                    </button>
                    <div class="flex items-center gap-1">
                        <template x-for="(page, index) in visiblePages" :key="index">
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
<!-- ========================================== -->
        <!-- MODAL DETAIL ASET (ADAPTIF PRINTER / LAPTOP) -->
        <!-- ========================================== -->
        <div x-show="isModalOpen" x-cloak
            x-effect="document.body.style.overflow = isModalOpen ? 'hidden' : ''"
            class="fixed inset-0 z-[9999] overflow-y-auto p-4 pt-10 sm:p-8 sm:pt-16">
            <div x-show="isModalOpen" x-transition.opacity class="fixed inset-0 bg-black/40" @click="isModalOpen = false"></div>

            <div x-show="isModalOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg mx-auto my-4 sm:my-8 p-6 sm:p-8 text-left">
                
                <button @click="isModalOpen = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>

                <!-- 1. HEADER KARTU -->
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight" x-text="detailAset?.asset_code || 'Tanpa Kode'"></h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 font-medium mt-1" x-text="`${detailAset?.name || 'Perangkat'} | ${detailAset?.type?.category?.name || 'Kategori'}`"></p>
                    
                    <div class="flex flex-wrap gap-2 mt-3" x-show="detailAset?.condition_status || detailAset?.placement_status">
                        <span class="px-2.5 py-1 text-xs font-medium bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded capitalize border border-gray-300 dark:border-gray-600" x-text="(detailAset?.condition_status || 'Good').replace('_', ' ')"></span>
                        <span class="px-2.5 py-1 text-xs font-medium bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded capitalize border border-gray-300 dark:border-gray-600" x-text="(detailAset?.placement_status || '').replace('_', ' ')"></span>
                    </div>
                </div>

                <div x-show="detailAset">
                    
                    <!-- LAYOUT PRINTER -->
                    <div x-show="(detailAset?.type?.category?.name || '').toLowerCase().includes('printer')">
                        <div class="mb-5">
                            <h3 class="text-[13px] font-semibold text-gray-500 dark:text-gray-400 mb-3 uppercase tracking-wider">Informasi Umum</h3>
                            <div class="grid grid-cols-2 gap-y-4 gap-x-4">
                                <div>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-0.5">Partner / Tim</p>
                                    <p class="text-xs font-bold text-gray-900 dark:text-white" x-text="detailAset?.partnership?.name || '-'"></p>
                                </div>
                                <div x-show="detailAset?.serial_number">
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-0.5">Serial number</p>
                                    <p class="text-xs font-bold text-gray-900 dark:text-white uppercase" x-text="detailAset?.serial_number || '-'"></p>
                                </div>
                                
                                <template x-for="(nilai, label) in specObject" :key="label">
                                    <div x-show="nilai !== null && nilai !== ''">
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-0.5 capitalize" x-text="label.replace(/_/g, ' ')"></p>
                                        <p class="text-xs font-bold text-gray-900 dark:text-white" x-text="nilai"></p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="w-full border-t border-gray-200 dark:border-gray-700 my-4"></div>
                        <div class="mb-5">
                            <h3 class="text-[13px] font-semibold text-gray-500 dark:text-gray-400 mb-3 uppercase tracking-wider">Consumable terkait</h3>
                            <a href="{{ route('consumables.index') }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">Lihat di stok consumable</a>
                        </div>
                    </div>

                    <!-- LAYOUT NON-PRINTER -->
                    <div x-show="!(detailAset?.type?.category?.name || '').toLowerCase().includes('printer')">
                        <div class="mb-5">
                            <h3 class="text-[13px] font-semibold text-gray-500 dark:text-gray-400 mb-3 uppercase tracking-wider">Informasi Umum</h3>
                            <div class="grid grid-cols-2 gap-y-4 gap-x-4">
                                
                                <!-- Selalu Tampil (Semua Aset) -->
                                <div>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-0.5">Partner / Tim</p>
                                    <p class="text-xs font-bold text-gray-900 dark:text-white" x-text="detailAset?.partnership?.name || '-'"></p>
                                </div>
                                
                                <!-- HANYA Tampil Jika Laptop/PC -->
                                <div x-show="((detailAset?.type?.category?.name || '').toLowerCase().includes('laptop') || (detailAset?.type?.name || '').toLowerCase().includes('laptop') || (detailAset?.type?.name || '').toLowerCase().includes('pc')) && detailAset?.serial_number">
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-0.5">Serial number</p>
                                    <p class="text-xs font-bold text-gray-900 dark:text-white uppercase" x-text="detailAset?.serial_number || '-'"></p>
                                </div>
                                <div x-show="((detailAset?.type?.category?.name || '').toLowerCase().includes('laptop') || (detailAset?.type?.name || '').toLowerCase().includes('laptop') || (detailAset?.type?.name || '').toLowerCase().includes('pc')) && detailAset?.purchase_date">
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-0.5">Tanggal pembelian</p>
                                    <p class="text-xs font-bold text-gray-900 dark:text-white" x-text="detailAset?.purchase_date"></p>
                                </div>
                                <div x-show="((detailAset?.type?.category?.name || '').toLowerCase().includes('laptop') || (detailAset?.type?.name || '').toLowerCase().includes('laptop') || (detailAset?.type?.name || '').toLowerCase().includes('pc')) && detailAset?.warranty_expired">
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-0.5">Garansi berakhir</p>
                                    <p class="text-xs font-bold text-gray-900 dark:text-white" x-text="detailAset?.warranty_expired"></p>
                                </div>
                                <div x-show="((detailAset?.type?.category?.name || '').toLowerCase().includes('laptop') || (detailAset?.type?.name || '').toLowerCase().includes('laptop') || (detailAset?.type?.name || '').toLowerCase().includes('pc')) && detailAset?.vendor">
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-0.5">Vendor</p>
                                    <p class="text-xs font-bold text-gray-900 dark:text-white" x-text="detailAset?.vendor"></p>
                                </div>
                                <div x-show="((detailAset?.type?.category?.name || '').toLowerCase().includes('laptop') || (detailAset?.type?.name || '').toLowerCase().includes('laptop') || (detailAset?.type?.name || '').toLowerCase().includes('pc')) && detailAset?.purchased_by">
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-0.5">Dibeli oleh</p>
                                    <p class="text-xs font-bold text-gray-900 dark:text-white" x-text="detailAset?.purchased_by"></p>
                                </div>
                            </div>
                        </div>

                        <div class="w-full border-t border-gray-200 dark:border-gray-700 my-4" x-show="detailAset?.specification"></div>

                        <div class="mb-5" x-show="Object.keys(specObject).length > 0">
                            <h3 class="text-[13px] font-semibold text-gray-500 dark:text-gray-400 mb-3 uppercase tracking-wider">Spesifikasi & Detail</h3>
                            <div class="grid grid-cols-2 gap-y-4 gap-x-4">
                                <template x-for="(nilai, label) in specObject" :key="label">
                                    <div x-show="nilai !== null && nilai !== ''">
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mb-0.5 capitalize" x-text="label.replace(/_/g, ' ')"></p>
                                        <p class="text-xs font-bold text-gray-900 dark:text-white" x-text="nilai"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="w-full border-t border-gray-200 dark:border-gray-700 my-4" x-show="detailAset?.condition_notes || detailAset?.assignments?.length > 0"></div>

                    <div class="grid grid-cols-1 gap-5 mb-2">
                        <div x-show="detailAset?.condition_notes">
                            <h3 class="text-[13px] font-semibold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wider">Catatan kondisi</h3>
                            <p class="text-xs font-bold text-gray-900 dark:text-white" x-text="detailAset?.condition_notes"></p>
                        </div>

                        <div x-show="detailAset?.assignments?.length > 0">
                            <h3 class="text-[13px] font-semibold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wider">Riwayat pemakaian</h3>
                            <div class="space-y-2">
                                <template x-for="(riwayat, idx) in (detailAset?.assignments || [])" :key="riwayat.id">
                                    <div class="bg-gray-50 dark:bg-gray-900/50 p-3 rounded-lg border border-gray-100 dark:border-gray-700">
                                        <div class="flex items-center justify-between">
                                            <p class="text-xs font-bold text-gray-900 dark:text-white">
                                                <span x-text="`${detailAset.assignments.length - idx}. `"></span>
                                                <span x-text="(riwayat.employee?.first_name || '-') + ' ' + (riwayat.employee?.last_name || '')"></span>
                                            </p>
                                            <span x-show="!riwayat.returned_at" class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400">Sedang dipakai</span>
                                            <span x-show="riwayat.returned_at" class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300">Sudah dikembalikan</span>
                                        </div>
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                            Diserahkan: <span x-text="riwayat.assigned_at ? new Date(riwayat.assigned_at).toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric'}) : '-'"></span>
                                            <span x-show="riwayat.returned_at">
                                                &middot; Dikembalikan: <span x-text="new Date(riwayat.returned_at).toLocaleDateString('id-ID', {day: '2-digit', month: 'short', year: 'numeric'})"></span>
                                            </span>
                                        </p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 1: FORM TAMBAH ASET (DINAMIS) -->
    <x-modal name="modal-add-asset" title="Form Tambah Aset Baru" maxWidth="lg">
        <form action="{{ route('assets.store') }}" method="POST" x-data="{
            selectedCategory: 'laptop',
            purchaseDate: '',
            warrantyDuration: '1', 
            customWarrantyMonths: '', 
            
            deviceName: '',
            isNameEdited: false,
            
            specs: [
                { key: 'Merk', value: '' }, { key: 'Tipe', value: '' },
                { key: 'Processor', value: '' }, { key: 'RAM', value: '' },
                { key: 'Storage', value: '' }, { key: 'OS', value: '' }
            ],

            init() {
                this.$watch('specs', () => {
                    if (!this.isNameEdited) {
                        let merk = this.specs.find(s => s.key.toLowerCase() === 'merk')?.value || '';
                        let tipe = this.specs.find(s => s.key.toLowerCase() === 'tipe')?.value || '';
                        let autoName = `${merk} ${tipe}`.trim();
                        if (autoName) this.deviceName = autoName;
                    }
                }, { deep: true });

                this.$watch('purchaseDate', (val) => {
                    if (!val) return;
                    let dateObj;
                    if (val.includes('-') && val.split('-')[0].length === 2) {
                        let parts = val.split('-');
                        dateObj = new Date(`${parts[2]}-${parts[1]}-${parts[0]}`);
                    } else {
                        dateObj = new Date(val);
                    }
                    let today = new Date();
                    today.setHours(0,0,0,0);
                    if (dateObj > today) {
                        alert('Tanggal pembelian tidak boleh melebihi hari ini!');
                        this.$nextTick(() => { this.purchaseDate = ''; });
                    }
                });
            },
            get formattedSpecs() {
                let specObject = {};
                this.specs.filter(s => s.key && s.value).forEach(s => {
                    specObject[s.key] = s.value;
                });
                return JSON.stringify(specObject);
            },

            get calculatedWarranty() {
                if (!this.purchaseDate) return '';
                let d = new Date(this.purchaseDate);
                let monthsToAdd = this.warrantyDuration === 'custom' 
                                ? parseInt(this.customWarrantyMonths || 0) 
                                : parseInt(this.warrantyDuration);
                d.setMonth(d.getMonth() + monthsToAdd);
                return d.toISOString().split('T')[0];
            },
            
            handleCategoryChange(e) {
                if (e.target.value === 'add_new') {
                    e.target.value = ''; 
                    $dispatch('close-modal', 'modal-add-asset'); 
                    setTimeout(() => {
                        $dispatch('open-modal', 'modal-add-category'); 
                    }, 300);
                    return;
                }
                
                let catName = e.target.options[e.target.selectedIndex].text.toLowerCase();
                this.selectedCategory = catName;
                
                if (catName.includes('laptop')) {
                    this.specs = [
                        { key: 'Merk', value: '' }, { key: 'Tipe', value: '' },
                        { key: 'Processor', value: '' }, { key: 'RAM', value: '' },
                        { key: 'Storage', value: '' }, { key: 'OS', value: '' }
                    ];
                } else if (catName.includes('printer')) {
                    this.specs = [
                        { key: 'Merk', value: '' }, { key: 'Tipe', value: '' },
                        { key: 'Jenis warna', value: '' }, { key: 'IP address', value: '' }
                    ];
                } else {
                    this.specs = [{ key: 'Merk', value: '' }, { key: 'Tipe', value: '' }];
                }
            }
        }">
            @if ($errors->any())
                <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400">
                    <span class="font-medium">Oops! Ada yang salah:</span>
                    <ul class="mt-1.5 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif    
            @csrf
            <input type="hidden" name="specification" :value="formattedSpecs">
            <input type="hidden" name="warranty_expired" :value="calculatedWarranty">

            <div class="space-y-4 text-left">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Kategori aset</label>
                        <select name="type_id" @change="handleCategoryChange($event)" required class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            <option value="">Pilih Kategori</option>
                            @foreach (\App\Models\AssetType::all()->unique('name') as $type)
                                <option value="{{ $type->id }}">{{ $type->name }} ({{$type->type_code }})</option>
                            @endforeach
                            <option value="add_new" class="font-semibold text-blue-600 bg-blue-50/50 dark:bg-blue-900/30 dark:text-blue-400">+ Tambah kategori baru</option>
                        </select>
                    </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Partner/tim</label>
                            <select name="partnership_id" class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                <option value="">Umum</option>
                                @foreach (\App\Models\Partnership::all() as $partner)
                                    <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                                @endforeach
                            </select>
                        </div>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Nama perangkat</label>
                    <input type="text" name="name" required placeholder="Otomatis, bisa diedit manual"
                        x-model="deviceName" 
                        @input="isNameEdited = true"
                        class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:placeholder-gray-400">
                </div>
                
                <!-- BLOK KODE ASET & SERIAL NUMBER (Serial Number HANYA untuk Laptop & Printer) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div :class="!(selectedCategory.includes('laptop') || selectedCategory.includes('printer')) ? 'md:col-span-2' : ''">
                        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Kode Aset</label>
                        <input type="text" class="w-full bg-gray-100 border border-gray-300 dark:bg-gray-700 dark:border-gray-600 rounded-lg px-3 py-2 text-gray-500 dark:text-gray-400 text-sm cursor-not-allowed" value="[ Dibuat Otomatis Setelah Disimpan ]" readonly disabled>
                    </div>
                    
                    <div x-show="selectedCategory.includes('laptop') || selectedCategory.includes('printer')" x-cloak>
                        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Serial Number</label>
                        <input type="text" name="serial_number" class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:placeholder-gray-400" placeholder="Masukkan Serial Number">
                    </div>
                </div>

                <!-- BLOK SPESIFIKASI -->
                <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg dark:bg-white/[0.02] dark:border-gray-700 mt-4">
                    <div class="flex items-center justify-between mb-3">
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Spesifikasi</label>
                        <button type="button" @click="specs.push({key: '', value: ''})" class="flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 transition-colors">
                            <span class="w-2 h-2 rounded-full bg-gray-800 dark:bg-white"></span> Tambah field
                        </button>
                    </div>
                    
                    <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                        <template x-for="(spec, index) in specs" :key="index">
                            <div class="flex items-center gap-2">
                                <input type="text" x-model="spec.key" placeholder="Contoh: Merk" class="w-1/3 px-3 py-1.5 text-sm border border-gray-300 rounded-lg bg-gray-100 focus:bg-white focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 dark:focus:bg-gray-600">
                                <input type="text" x-model="spec.value" placeholder="Isi..." class="w-full px-3 py-1.5 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:placeholder-gray-400">
                                <button type="button" @click="specs.splice(index, 1)" class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg dark:hover:bg-red-500/10 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- BLOK VENDOR DLL (HANYA TAMPIL UNTUK LAPTOP & PRINTER) -->
                <div x-show="selectedCategory.includes('laptop') || selectedCategory.includes('printer')" x-cloak class="space-y-4 mt-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Vendor</label>
                            <input type="text" name="vendor" placeholder="Nama vendor" class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:placeholder-gray-400">
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Dibeli oleh</label>
                            <input type="text" name="purchased_by" placeholder="Nama/tim" class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:placeholder-gray-400">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Pembelian</label>
                            <input type="text" name="purchase_date" x-model="purchaseDate" x-init="flatpickr($el, { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd-m-Y', maxDate: 'today' })" placeholder="Pilih Tanggal..." class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:placeholder-gray-400">
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Durasi Garansi</label>
                            <div class="flex gap-2">
                                <select x-model="warrantyDuration" class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                                    <option value="1">1 bulan</option><option value="3">3 bulan</option><option value="6">6 bulan</option><option value="12">1 tahun</option><option value="24">2 tahun</option><option value="custom" class="font-semibold text-blue-600 dark:text-blue-400">Lainnya...</option>
                                </select>
                                <div x-show="warrantyDuration === 'custom'" x-cloak class="flex items-center gap-1.5 w-full">
                                    <input type="number" min="1" x-model="customWarrantyMonths" placeholder="0" class="w-16 px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 text-center">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">bln</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center justify-start mt-6 pt-4">
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white transition-colors duration-200 bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-slate-900">
                        Simpan aset
                    </button>
                </div>
            </div>
        </form>
    </x-modal>

    <!-- MODAL 2: FORM KATEGORI BARU -->
    <x-modal name="modal-add-category" title="Kategori Baru" maxWidth="sm">
        <form action="{{ route('asset-types.store') }}" method="POST" class="p-2">
            @csrf
            <input type="hidden" name="category_id" value="1"> 
            <div class="space-y-4 text-left">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Nama kategori</label>
                    <input type="text" name="name" placeholder="Contoh: Keyboard" required class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 transition-colors">
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Alur dokumen</label>
                    <select name="document_flow" required class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white transition-colors">
                        <option value="serah_terima">SKPPI (aset IT lain)</option>
                        <option value="logbook">Logbook peminjaman (proyektor)</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-3 mt-6">
                <button type="submit" class="rounded-lg bg-gray-600 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 dark:bg-gray-500 dark:hover:bg-gray-400 transition-colors">
                    Simpan kategori
                </button>
                <button type="button" @click="$dispatch('close-modal', 'modal-add-category'); setTimeout(() =>$dispatch('open-modal', 'modal-add-asset'), 300)" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </x-modal>
@endsection