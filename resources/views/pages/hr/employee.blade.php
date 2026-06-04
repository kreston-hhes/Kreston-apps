@extends('layouts.app')

@section('content')

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <div class="w-full space-y-6 relative" x-data="{
    
        // Tab
        activeTab: '{{ request('tab', 'active') }}',
    
        // Data
        activeData: @js($activeData),
        resignedData: @js($resignedData),
    
        perPage: 5,
        currentPage: 1,
    
        get currentDataset() {
            return this.activeTab === 'active' ? this.activeData : this.resignedData;
        },
    
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
    
        // Generate deret angka
        get visiblePages() {
            let c = this.currentPage;
            let t = this.totalPages;
            if (t <= 5) return Array.from({ length: t }, (_, i) => i + 1); // Tampil semua jika <= 5
            if (c <= 3) return [1, 2, 3, 4, '...', t]; // Posisi di awal
            if (c >= t - 2) return [1, '...', t - 3, t - 2, t - 1, t]; // Posisi di akhir
            return [1, '...', c - 1, c, c + 1, '...', t]; // Posisi di tengah
        },
    
        // Modal Create/Edit
        showModal: false,
        modalMode: 'create',
        isSubmitting: false,
        form: {
            id: null,
            nik: '',
            first_name: '',
            last_name: '',
            gender: '',
            position: '',
            division: '',
            partnership_id: '',
            manager_id: '',
            date_of_entry: '',
        },
        errors: {},
    
        // Modal Resign
        showResignModal: false,
        isResigning: false,
        resignForm: { id: null, name: '', release_date: '', entry_date: '' },
        resignError: '',
        resignPicker: null,
    
        // Open RESIGN modal
        openResign(row) {
            this.resignForm = { id: row.id, name: row.employeeName, release_date: '', entry_date: row.date_of_entry_raw };
            this.resignError = '';
            this.showResignModal = true;
    
            if (this.resignPicker) {
                this.resignPicker.set('minDate', row.date_of_entry_raw);
                this.resignPicker.clear();
            }
        },
    
        // Open CREATE
        openCreate() {
            this.modalMode = 'create';
            this.errors = {};
            this.form = {
                id: null,
                nik: '',
                first_name: '',
                last_name: '',
                gender: '',
                position: '',
                division: '',
                partnership_id: '',
                manager_id: '',
                date_of_entry: ''
            };
            this.showModal = true;
        },
    
        // Open EDIT
        openEdit(row) {
            this.modalMode = 'edit';
            this.errors = {};
            this.form = {
                id: row.id,
                nik: row.nik ?? '',
                first_name: row.first_name ?? '',
                last_name: row.last_name ?? '',
                gender: row.gender ?? '',
                position: row.position ?? '',
                division: row.division ?? '',
                partnership_id: row.partnership_id ?? '',
                manager_id: row.manager_id ?? '',
                date_of_entry: row.date_of_entry_raw ?? '',
            };
            this.showModal = true;
        },
    
        // Submit Create/Edit
        async submitForm() {
            this.isSubmitting = true;
            this.errors = {};
            const isEdit = this.modalMode === 'edit';
            const url = isEdit ? `/employees/${this.form.id}` : `/employees`;
            const method = isEdit ? 'PUT' : 'POST';
            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.form),
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    if (isEdit) {
                        const idx = this.activeData.findIndex(r => r.id === this.form.id);
                        if (idx !== -1) this.activeData[idx] = data.employee;
                    } else {
                        this.activeData.unshift(data.employee);
                    }
                    this.showModal = false;
                    window.showNotification?.({
                        variant: 'success',
                        title: isEdit ? 'Update Berhasil' : 'Tambah Berhasil',
                        message: data.message,
                    });
                } else if (res.status === 422) {
                    this.errors = data.errors ?? {};
                } else {
                    alert(data.message ?? 'Terjadi kesalahan.');
                }
            } catch (err) {
                console.error(err);
                alert('Gagal menghubungi server.');
            } finally {
                this.isSubmitting = false;
            }
        },
    
    
        // Submit RESIGN
        async submitResign() {
            if (!this.resignForm.release_date) {
                this.resignError = 'Tanggal resign wajib diisi.';
                return;
            }
            this.isResigning = true;
            this.resignError = '';
            try {
                const res = await fetch(`/employees/${this.resignForm.id}/resign`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ release_date: this.resignForm.release_date }),
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    /* Pindah dari activeData ke resignedData */
                    const idx = this.activeData.findIndex(r => r.id === this.resignForm.id);
                    if (idx !== -1) {
                        const moved = { ...this.activeData[idx], ...data.employee };
                        this.activeData.splice(idx, 1);
                        this.resignedData.unshift(moved);
                    }
                    this.showResignModal = false;
                    window.showNotification?.({
                        variant: 'success',
                        title: 'Resign Berhasil',
                        message: data.message,
                    });
                } else {
                    this.resignError = data.message ?? 'Terjadi kesalahan.';
                }
            } catch (err) {
                console.error(err);
                this.resignError = 'Gagal menghubungi server.';
            } finally {
                this.isResigning = false;
            }
        },
    
        // Delete
        deleteRow(id, name) {
            if (!confirm(`Yakin ingin menghapus karyawan: ${name}?\nAksi ini tidak bisa dibatalkan.`)) return;
            fetch(`/employees/${id}`, {
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
                        this.activeData = this.activeData.filter(r => r.id !== id);
                        window.showNotification?.({
                            variant: 'success',
                            title: 'Delete Berhasil',
                            message: `${name} berhasil dihapus.`,
                        });
                    }
                })
                .catch(() => alert('Gagal menghapus data.'));
        },
    }">

        {{-- MODAL CREATE / EDIT --}}
        <div x-show="showModal" x-cloak
            class="absolute inset-0 z-50 w-screen h-screen overflow-y-auto bg-black/50 backdrop-blur-sm px-4 pt-2  pb-10"
            @keydown.escape.window="showModal = false">

            <div class="relative w-full max-w-lg bg-white dark:bg-gray-800 border border-transparent dark:border-gray-700 rounded-2xl shadow-2xl my-8 flex flex-col"
                @click.stop>

                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white"
                        x-text="modalMode === 'create' ? 'Add New Employee' : 'Edit Employee'"></h2>
                    <button @click="showModal = false"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">NIK <span
                                    class="text-red-500">*</span></label>
                            <input type="text" x-model="form.nik" placeholder="e.g. EMP-0001"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white dark:border-gray-600"
                                :class="errors.nik ? 'border-red-400' : 'border-gray-300'">
                            <p x-show="errors.nik" x-text="errors.nik?.[0]" class="mt-1 text-xs text-red-500"></p>
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">Entry Date <span
                                    class="text-red-500">*</span></label>
                            <input type="text" x-model="form.date_of_entry" x-init="flatpickr($el, {
                                dateFormat: 'Y-m-d',
                                altInput: true,
                                altFormat: 'd M Y',
                                maxDate: 'today' /* 🟢 Kalender di masa depan akan dikunci/abu-abu */
                            })"
                                placeholder="Pilih Tanggal Masuk..."
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white dark:border-gray-600"
                                :class="errors.date_of_entry ? 'border-red-400' : 'border-gray-300'">
                            <p x-show="errors.date_of_entry" x-text="errors.date_of_entry?.[0]"
                                class="mt-1 text-xs text-red-500"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">First Name <span
                                    class="text-red-500">*</span></label>
                            <input type="text" x-model="form.first_name" placeholder="First name"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white dark:border-gray-600"
                                :class="errors.first_name ? 'border-red-400' : 'border-gray-300'">
                            <p x-show="errors.first_name" x-text="errors.first_name?.[0]" class="mt-1 text-xs text-red-500">
                            </p>
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">Last Name <span
                                    class="text-red-500">*</span></label>
                            <input type="text" x-model="form.last_name" placeholder="Last name"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white dark:border-gray-600"
                                :class="errors.last_name ? 'border-red-400' : 'border-gray-300'">
                            <p x-show="errors.last_name" x-text="errors.last_name?.[0]" class="mt-1 text-xs text-red-500">
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">Gender <span
                                    class="text-red-500">*</span></label>
                            <select x-model="form.gender"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white dark:border-gray-600"
                                :class="errors.gender ? 'border-red-400' : 'border-gray-300'">
                                <option value="">— Select Gender —</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                            <p x-show="errors.gender" x-text="errors.gender?.[0]" class="mt-1 text-xs text-red-500"></p>
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">Position <span
                                    class="text-red-500">*</span></label>
                            <select x-model="form.position"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white dark:border-gray-600"
                                :class="errors.position ? 'border-red-400' : 'border-gray-300'">
                                <option value="">— Select Position —</option>
                                @foreach ($positions as $pos)
                                    <option value="{{ $pos }}">{{ $pos }}</option>
                                @endforeach
                            </select>
                            <p x-show="errors.position" x-text="errors.position?.[0]" class="mt-1 text-xs text-red-500"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">Division <span
                                    class="text-red-500">*</span></label>
                            <select x-model="form.division"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white dark:border-gray-600"
                                :class="errors.division ? 'border-red-400' : 'border-gray-300'">
                                <option value="">— Select Division —</option>
                                @foreach ($divisions as $div)
                                    <option value="{{ $div }}">{{ $div }}</option>
                                @endforeach
                            </select>
                            <p x-show="errors.division" x-text="errors.division?.[0]" class="mt-1 text-xs text-red-500"></p>
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">Team <span
                                    class="text-red-500">*</span></label>
                            <select x-model="form.partnership_id"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white dark:border-gray-600"
                                :class="errors.partnership_id ? 'border-red-400' : 'border-gray-300'">
                                <option value="">— Select Team —</option>
                                @if (isset($partnershipOptions))
                                    @foreach ($partnershipOptions as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <p x-show="errors.partnership_id" x-text="errors.partnership_id?.[0]"
                                class="mt-1 text-xs text-red-500"></p>
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">Manager <span
                                class="text-red-500">*</span></label>
                        <select x-model="form.manager_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:text-white dark:border-gray-600"
                            :class="errors.manager_id ? 'border-red-400' : 'border-gray-300'">
                            <option value="">— No Manager —</option>
                            @if (isset($managers))
                                @foreach ($managers as $mgr)
                                    <option value="{{ $mgr->id }}">{{ $mgr->first_name }} {{ $mgr->last_name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <p x-show="errors.manager_id" x-text="errors.manager_id?.[0]" class="mt-1 text-xs text-red-500">
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                    <button @click="showModal = false"
                        class="px-4 py-2 text-sm rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button @click="submitForm()" :disabled="isSubmitting"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed transition-colors flex items-center gap-2">
                        <svg x-show="isSubmitting" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        <span
                            x-text="isSubmitting ? 'Saving...' : (modalMode === 'create' ? 'Add Employee' : 'Save Changes')"></span>
                    </button>
                </div>
            </div>
        </div>
        {{-- MODAL RESIGN --}}
        <div x-show="showResignModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
            @keydown.escape.window="showResignModal = false">
            <div class="relative w-full max-w-sm mx-4 bg-white dark:bg-gray-900 rounded-2xl shadow-2xl overflow-hidden"
                @click.stop>
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/[0.05]">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white">Konfirmasi Resign</h2>
                    <button @click="showResignModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <!-- Body -->
                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Tandai <span class="font-semibold text-gray-800 dark:text-white" x-text="resignForm.name"></span>
                        sebagai karyawan resign.
                        Data tidak akan dihapus dari sistem.
                    </p>
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">Tanggal Resign <span
                                class="text-red-500">*</span></label>
                        <input type="text" x-model="resignForm.release_date" x-init="resignPicker = flatpickr($el, {
                            dateFormat: 'Y-m-d',
                            altInput: true,
                            altFormat: 'd M Y',
                            maxDate: 'today'
                        })"
                            placeholder="Pilih Tanggal Resign..."
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 dark:bg-gray-800 dark:text-white dark:border-gray-700"
                            :class="resignError ? 'border-red-400' : 'border-gray-300'">
                        <p x-show="resignError" x-text="resignError" class="mt-1 text-xs text-red-500"></p>
                    </div>
                </div>
                <!-- Footer -->
                <div
                    class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-white/[0.05]">
                    <button @click="showResignModal = false"
                        class="px-4 py-2 text-sm rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 transition-colors">
                        Cancel
                    </button>
                    <button @click="submitResign()" :disabled="isResigning"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed transition-colors flex items-center gap-2">
                        <svg x-show="isResigning" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        <span x-text="isResigning ? 'Memproses...' : 'Konfirmasi Resign'"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- MAIN CARD --}}
        <div class="bg-white border border-gray-200 rounded-2xl dark:bg-white/[0.03] dark:border-white/[0.05]">

            <!-- Tabs -->
            <div class="flex items-center gap-1 px-6 pt-5 border-b border-gray-100 dark:border-white/[0.05]">
                <button @click="activeTab = 'active'; currentPage = 1"
                    :class="activeTab === 'active'
                        ?
                        'border-b-2 border-blue-600 text-blue-600 font-semibold' :
                        'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                    class="pb-3 px-4 text-sm transition-colors">
                    Active Employees
                    <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full"
                        :class="activeTab === 'active' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500'"
                        x-text="activeData.length">{{ $totalActive }}</span>
                </button>
                <button @click="activeTab = 'resigned'; currentPage = 1"
                    :class="activeTab === 'resigned'
                        ?
                        'border-b-2 border-orange-500 text-orange-500 font-semibold' :
                        'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                    class="pb-3 px-4 text-sm transition-colors">
                    Resigned
                    <span class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full"
                        :class="activeTab === 'resigned' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-500'"
                        x-text="resignedData.length">{{ $totalResigned }}</span>
                </button>
            </div>

            <!-- Filter & Search -->
            <div class="p-6 border-b border-gray-100 dark:border-white/[0.05]">
                <div class="flex flex-wrap items-center justify-between gap-4">

                    <form action="{{ route('employees.index') }}" method="GET"
                        class="flex flex-wrap items-center gap-3">

                        <div class="relative w-full sm:w-64">
                            <span class="absolute -translate-y-1/2 left-3 top-1/2 text-gray-400">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2" />
                                </svg>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Search NIK or Name..."
                                class="pl-10 dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        </div>

                        <select name="position" onchange="this.form.submit()"
                            class="px-3 py-2 border border-gray-200 rounded-lg text-sm dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 border-gray-300 bg-transparent text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">All Positions</option>
                            @foreach ($positions as $pos)
                                <option value="{{ $pos }}" {{ request('position') == $pos ? 'selected' : '' }}>
                                    {{ $pos }}</option>
                            @endforeach
                        </select>

                        <select name="partnership" onchange="this.form.submit()"
                            class="px-3 py-2 border border-gray-200 rounded-lg text-sm dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 border-gray-300 bg-transparent text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">All Teams</option>
                            @foreach ($partnerships as $partnership)
                                <option value="{{ $partnership }}"
                                    {{ request('partnership') == $partnership ? 'selected' : '' }}>{{ $partnership }}
                                </option>
                            @endforeach
                        </select>

                        <select name="division" onchange="this.form.submit()"
                            class="px-3 py-2 border border-gray-200 rounded-lg text-sm dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 border-gray-300 bg-transparent text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">All Divisions</option>
                            @foreach ($divisions as $div)
                                <option value="{{ $div }}" {{ request('division') == $div ? 'selected' : '' }}>
                                    {{ $div }}</option>
                            @endforeach
                        </select>

                        @if (request()->anyFilled(['search', 'position', 'division', 'partnership']))
                            <a href="{{ route('employees.index') }}"
                                class="text-sm text-red-500 hover:text-red-700">Clear</a>
                        @endif
                    </form>

                    @can('manage', App\Models\Employee::class)
                        <button @click="openCreate()"
                            class="inline-flex justify-center items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Employee
                        </button>
                    @endcan

                </div>
            </div>

            <!-- ── Tab Active── -->
            <div x-show="activeTab === 'active'">
                <div class="max-w-full overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-start">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'nik', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'tab' => 'active']) }}"
                                        class="flex items-center gap-1 text-theme-xs font-medium text-gray-500 hover:text-blue-600">
                                        NIK <svg class="size-3" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" stroke-width="2" />
                                        </svg>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-start">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'first_name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'tab' => 'active']) }}"
                                        class="flex items-center gap-1 text-theme-xs font-medium text-gray-500 hover:text-blue-600">
                                        Name <svg class="size-3" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" stroke-width="2" />
                                        </svg>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-start text-theme-xs font-medium text-gray-500">Division</th>
                                <th class="px-6 py-3 text-start">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'partnership', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'tab' => 'active']) }}"
                                        class="flex items-center gap-1 text-theme-xs font-medium text-gray-500 hover:text-blue-600">
                                        Teams <svg class="size-3" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" stroke-width="2" />
                                        </svg>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-start text-theme-xs font-medium text-gray-500">Manager</th>
                                <th class="px-6 py-3 text-start">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'date_of_entry', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'tab' => 'active']) }}"
                                        class="flex items-center gap-1 text-theme-xs font-medium text-gray-500 hover:text-blue-600">
                                        Entry Date <svg class="size-3" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" stroke-width="2" />
                                        </svg>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-start text-theme-xs font-medium text-gray-500">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in paginatedData" :key="row.id || index">
                                <tr
                                    class="border-b border-gray-100 dark:border-white/[0.05] hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                    <td class="px-6 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400"
                                        x-text="row.nik"></td>
                                    <td class="px-6 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-medium"
                                                :class="[row.avatarBg, row.avatarColor]">
                                                <span x-text="row.initials"></span>
                                            </div>
                                            <div>
                                                <span
                                                    class="block text-theme-sm font-medium text-gray-700 dark:text-gray-400"
                                                    x-text="row.employeeName"></span>
                                                <span class="text-xs text-gray-500" x-text="row.position"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400"
                                        x-text="row.division"></td>
                                    <td class="px-6 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400"
                                        x-text="row.partnership"></td>
                                    <td class="px-6 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400"
                                        x-text="row.manager"></td>
                                    <td class="px-6 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400"
                                        x-text="row.entry_date"></td>
                                    <td class="px-6 py-3.5">
                                        @can('manage', App\Models\Employee::class)
                                            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                                <!-- Trigger ⋮ -->
                                                <button @click="open = !open"
                                                    class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-white/[0.05] transition-colors">
                                                    <svg class="size-5" fill="currentColor" viewBox="0 0 24 24">
                                                        <circle cx="12" cy="5" r="1.5" />
                                                        <circle cx="12" cy="12" r="1.5" />
                                                        <circle cx="12" cy="19" r="1.5" />
                                                    </svg>
                                                </button>
                                                <!-- Dropdown -->
                                                <div x-show="open" x-cloak
                                                    x-transition:enter="transition ease-out duration-100"
                                                    x-transition:enter-start="opacity-0 scale-95"
                                                    x-transition:enter-end="opacity-100 scale-100"
                                                    x-transition:leave="transition ease-in duration-75"
                                                    x-transition:leave-start="opacity-100 scale-100"
                                                    x-transition:leave-end="opacity-0 scale-95"
                                                    class="absolute right-0 z-30 mt-1 w-44 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-white/[0.08] py-1">

                                                    <!-- Edit Data -->
                                                    <button @click="openEdit(row); open = false"
                                                        class="flex w-full items-center gap-2.5 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/[0.05] transition-colors">
                                                        <svg class="size-4 text-blue-500" fill="none" viewBox="0 0 24 24"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                        Edit Data
                                                    </button>

                                                    <!-- Divider -->
                                                    <div class="my-1 border-t border-gray-100 dark:border-white/[0.06]"></div>

                                                    <!-- Resign -->
                                                    <button @click="openResign(row); open = false"
                                                        class="flex w-full items-center gap-2.5 px-4 py-2 text-sm text-orange-600 hover:bg-orange-50 dark:hover:bg-orange-500/10 transition-colors">
                                                        <svg class="size-4" fill="none" viewBox="0 0 24 24"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                        </svg>
                                                        Tandai Resign
                                                    </button>

                                                    <!-- Hapus -->
                                                    <button @click="deleteRow(row.id, row.employeeName); open = false"
                                                        class="flex w-full items-center gap-2.5 px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                                                        <svg class="size-4" fill="none" viewBox="0 0 24 24"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        Hapus Data
                                                    </button>

                                                </div>
                                            </div>
                                        @endcan
                                    </td>
                                </tr>
                            </template>
                            <template x-if="activeData.length === 0">
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-400">No active
                                        employees found.</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── Tab Resigned ── -->
            <div x-show="activeTab === 'resigned'">
                <div class="max-w-full overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-start">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'nik', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'tab' => 'resigned']) }}"
                                        class="flex items-center gap-1 text-theme-xs font-medium text-gray-500 hover:text-blue-600">
                                        NIK <svg class="size-3" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" stroke-width="2" />
                                        </svg>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-start">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'first_name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'tab' => 'resigned']) }}"
                                        class="flex items-center gap-1 text-theme-xs font-medium text-gray-500 hover:text-blue-600">
                                        Name <svg class="size-3" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" stroke-width="2" />
                                        </svg>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-start text-theme-xs font-medium text-gray-500">Division</th>
                                <th class="px-6 py-3 text-start">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'partnership', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'tab' => 'resigned']) }}"
                                        class="flex items-center gap-1 text-theme-xs font-medium text-gray-500 hover:text-blue-600">
                                        Teams <svg class="size-3" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" stroke-width="2" />
                                        </svg>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-start text-theme-xs font-medium text-gray-500">Position</th>
                                <th class="px-6 py-3 text-start">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'release_date', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc', 'tab' => 'resigned']) }}"
                                        class="flex items-center gap-1 text-theme-xs font-medium text-gray-500 hover:text-blue-600">
                                        Resign Date <svg class="size-3" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" stroke-width="2" />
                                        </svg>
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in paginatedData" :key="row.id || index">
                                <tr
                                    class="border-b border-gray-100 dark:border-white/[0.05] hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                    <td class="px-6 py-3.5 text-theme-sm text-gray-500 dark:text-gray-500"
                                        x-text="row.nik"></td>
                                    <td class="px-6 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-medium bg-gray-100 text-gray-400">
                                                <span x-text="row.initials"></span>
                                            </div>
                                            <div>
                                                <span
                                                    class="block text-theme-sm font-medium text-gray-500 dark:text-gray-500"
                                                    x-text="row.employeeName"></span>
                                                <span class="text-xs text-gray-400" x-text="row.position"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3.5 text-theme-sm text-gray-500 dark:text-gray-500"
                                        x-text="row.division"></td>
                                    <td class="px-6 py-3.5 text-theme-sm text-gray-500 dark:text-gray-500"
                                        x-text="row.partnership"></td>
                                    <td class="px-6 py-3.5 text-theme-sm text-gray-500 dark:text-gray-500"
                                        x-text="row.position"></td>
                                    <td class="px-6 py-3.5">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-600"
                                            x-text="row.release_date"></span>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="resignedData.length === 0">
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">No resigned
                                        employees found.</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div
                class="flex flex-col items-center justify-between gap-4 px-6 py-4 border-t border-gray-100 sm:flex-row dark:border-white/[0.05]">

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
                                    'text-gray-600 border-transparent hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/[0.05]': currentPage !==
                                        page && page !== '...',
                                    'text-gray-400 cursor-default border-transparent': page === '...'
                                }"
                                class="px-3 py-1.5 text-sm font-medium border rounded-lg transition-colors" x-text="page"
                                :disabled="page === '...'">
                            </button>
                        </template>
                    </div>

                    <button @click="currentPage++" :disabled="currentPage === totalPages"
                        class="px-3 py-1.5 text-sm font-medium border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                        Next
                    </button>

                    <div
                        class="flex items-center gap-2 pl-2 sm:pl-4 sm:ml-2 sm:border-l border-gray-200 dark:border-white/[0.05]">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Go to</span>
                        <input type="number" min="1" :max="totalPages"
                            @keyup.enter="let p = parseInt($event.target.value); if(p >= 1 && p <= totalPages) { currentPage = p; } $event.target.value = ''"
                            @blur="let p = parseInt($event.target.value); if(p >= 1 && p <= totalPages) { currentPage = p; } $event.target.value = ''"
                            class="w-12 h-8 px-1 text-sm text-center bg-transparent border border-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-700 dark:text-white"
                            placeholder="#">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
