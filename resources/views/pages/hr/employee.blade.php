@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Employee" />

<div class="w-full space-y-6" x-data="{
    tableRowData: @js($tableData),

    /* ── Modal state ── */
    showModal: false,
    modalMode: 'create',   // 'create' | 'edit'
    isSubmitting: false,
    form: {
        id: null,
        nik: '',
        first_name: '',
        last_name: '',
        position: '',
        division: '',
        partnership: '',
        manager_id: '',
        date_of_entry: '',
    },
    errors: {},

    /* ── Open CREATE modal ── */
    openCreate() {
        this.modalMode = 'create';
        this.errors    = {};
        this.form = { id: null, nik: '', first_name: '', last_name: '',
                      position: '', division: '', partnership: '',
                      manager_id: '', date_of_entry: '' };
        this.showModal = true;
    },

    /* ── Open EDIT modal ── */
    openEdit(row) {
        this.modalMode = 'edit';
        this.errors    = {};
        this.form = {
            id:           row.id,
            nik:          row.nik          ?? '',
            first_name:   row.first_name   ?? '',
            last_name:    row.last_name    ?? '',
            position:     row.position     ?? '',
            division:     row.division     ?? '',
            partnership:  row.partnership  ?? '',
            manager_id:   row.manager_id   ?? '',
            date_of_entry: row.date_of_entry_raw ?? '',
        };
        this.showModal = true;
    },

    /* ── Submit (create or update) ── */
    async submitForm() {
        this.isSubmitting = true;
        this.errors = {};

        const isEdit = this.modalMode === 'edit';
        const url    = isEdit
            ? `/employees/${this.form.id}`
            : `/employees`;
        const method = isEdit ? 'PUT' : 'POST';

        try {
            const res  = await fetch(url, {
                method,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept':       'application/json',
                },
                body: JSON.stringify(this.form),
            });
            const data = await res.json();

            if (res.ok && data.success) {
                if (isEdit) {
                    /* Update row in-place */
                    const idx = this.tableRowData.findIndex(r => r.id === this.form.id);
                    if (idx !== -1) this.tableRowData[idx] = data.employee;
                } else {
                    /* Prepend new row */
                    this.tableRowData.unshift(data.employee);
                }
                this.showModal = false;
                window.showNotification?.({
                    variant: 'success',
                    title:   isEdit ? 'Update Berhasil' : 'Tambah Berhasil',
                    message: data.message,
                });
            } else if (res.status === 422) {
                /* Validation errors from Laravel */
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

    /* ── Delete ── */
    deleteRow(id, name) {
        if (!confirm(`Are you sure you want to delete employee: ${name}?`)) return;

        fetch(`/employees/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept':       'application/json',
            },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                this.tableRowData = this.tableRowData.filter(r => r.id !== id);
                window.showNotification?.({
                    variant: 'success',
                    title:   'Delete Berhasil',
                    message: `${name} deleted successfully!`,
                });
            }
        })
        .catch(() => alert('Gagal menghapus data.'));
    },
}">

    <!-- ════════════════════════════════════════════════════════
         MODAL (Create / Edit)
         ════════════════════════════════════════════════════════ -->
    <div
        x-show="showModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
        @keydown.escape.window="showModal = false"
    >
        <div
            class="relative w-full max-w-2xl mx-4 bg-white dark:bg-gray-900 rounded-2xl shadow-2xl overflow-hidden"
            @click.stop
        >
            <!-- Modal header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/[0.05]">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white"
                    x-text="modalMode === 'create' ? 'Add New Employee' : 'Edit Employee'"></h2>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal body -->
            <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">

                <!-- Row 1: NIK & Entry Date -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">NIK <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.nik" placeholder="e.g. 001234"
                            class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                   dark:bg-gray-800 dark:text-white dark:border-gray-700"
                            :class="errors.nik ? 'border-red-400' : 'border-gray-300'">
                        <p x-show="errors.nik" x-text="errors.nik?.[0]" class="mt-1 text-xs text-red-500"></p>
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">Entry Date <span class="text-red-500">*</span></label>
                        <input type="date" x-model="form.date_of_entry"
                            class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                   dark:bg-gray-800 dark:text-white dark:border-gray-700"
                            :class="errors.date_of_entry ? 'border-red-400' : 'border-gray-300'">
                        <p x-show="errors.date_of_entry" x-text="errors.date_of_entry?.[0]" class="mt-1 text-xs text-red-500"></p>
                    </div>
                </div>

                <!-- Row 2: First Name & Last Name -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">First Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.first_name" placeholder="First name"
                            class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                   dark:bg-gray-800 dark:text-white dark:border-gray-700"
                            :class="errors.first_name ? 'border-red-400' : 'border-gray-300'">
                        <p x-show="errors.first_name" x-text="errors.first_name?.[0]" class="mt-1 text-xs text-red-500"></p>
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">Last Name</label>
                        <input type="text" x-model="form.last_name" placeholder="Last name"
                            class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                   dark:bg-gray-800 dark:text-white dark:border-gray-700"
                            :class="errors.last_name ? 'border-red-400' : 'border-gray-300'">
                        <p x-show="errors.last_name" x-text="errors.last_name?.[0]" class="mt-1 text-xs text-red-500"></p>
                    </div>
                </div>

                <!-- Row 3: Position & Division -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">Position</label>
                        <select x-model="form.position"
                            class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                   dark:bg-gray-800 dark:text-white dark:border-gray-700 border-gray-300">
                            <option value="">— Select Position —</option>
                            @foreach($positions as $pos)
                                <option value="{{ $pos }}">{{ $pos }}</option>
                            @endforeach
                        </select>
                        <p x-show="errors.position" x-text="errors.position?.[0]" class="mt-1 text-xs text-red-500"></p>
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">Division</label>
                        <select x-model="form.division"
                            class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                   dark:bg-gray-800 dark:text-white dark:border-gray-700 border-gray-300">
                            <option value="">— Select Division —</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div }}">{{ $div }}</option>
                            @endforeach
                        </select>
                        <p x-show="errors.division" x-text="errors.division?.[0]" class="mt-1 text-xs text-red-500"></p>
                    </div>
                </div>

                <!-- Row 4: Team & Manager -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">Team (Partnership)</label>
                        <select x-model="form.partnership"
                            class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                   dark:bg-gray-800 dark:text-white dark:border-gray-700 border-gray-300">
                            <option value="">— Select Team —</option>
                            @foreach($partnerships as $p)
                                <option value="{{ $p }}">{{ $p }}</option>
                            @endforeach
                        </select>
                        <p x-show="errors.partnership" x-text="errors.partnership?.[0]" class="mt-1 text-xs text-red-500"></p>
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">Manager</label>
                        <select x-model="form.manager_id"
                            class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500
                                   dark:bg-gray-800 dark:text-white dark:border-gray-700 border-gray-300">
                            <option value="">— No Manager —</option>
                            @foreach($managers as $mgr)
                                <option value="{{ $mgr->id }}">{{ $mgr->first_name }} {{ $mgr->last_name }}</option>
                            @endforeach
                        </select>
                        <p x-show="errors.manager_id" x-text="errors.manager_id?.[0]" class="mt-1 text-xs text-red-500"></p>
                    </div>
                </div>

            </div><!-- end body -->

            <!-- Modal footer -->
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-white/[0.05]">
                <button @click="showModal = false"
                    class="px-4 py-2 text-sm rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.05] transition-colors">
                    Cancel
                </button>
                <button @click="submitForm()" :disabled="isSubmitting"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed transition-colors flex items-center gap-2">
                    <svg x-show="isSubmitting" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span x-text="isSubmitting ? 'Saving...' : (modalMode === 'create' ? 'Add Employee' : 'Save Changes')"></span>
                </button>
            </div>
        </div>
    </div>
    <!-- END MODAL -->

    <!-- ════════════════════════════════════════════════════════
         MAIN CARD
         ════════════════════════════════════════════════════════ -->
    <div class="bg-white border border-gray-200 rounded-2xl dark:bg-white/[0.03] dark:border-white/[0.05]">

        <!-- Header & Filter Section -->
        <div class="p-6 border-b border-gray-100 dark:border-white/[0.05]">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                <div class="flex flex-wrap items-center gap-3">
                    <!-- Global Search Form -->
                    <form action="{{ route('employees.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                        <div class="relative w-full sm:w-64">
                            <span class="absolute -translate-y-1/2 left-3 top-1/2 text-gray-400">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2"/></svg>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search NIK or Name..."
                                class="pl-10 dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        </div>

                        <select name="position" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-lg text-sm dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 border-gray-300 bg-transparent bg-none text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            <option value="">All Positions</option>
                            @foreach($positions as $pos)
                                <option value="{{ $pos }}" {{ request('position') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                            @endforeach
                        </select>

                        <select name="partnership" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-lg text-sm dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 border-gray-300 bg-transparent bg-none text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            <option value="">All Teams</option>
                            @foreach($partnerships as $partnership)
                                <option value="{{ $partnership }}" {{ request('partnership') == $partnership ? 'selected' : '' }}>{{ $partnership }}</option>
                            @endforeach
                        </select>

                        <select name="division" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-lg text-sm dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 border-gray-300 bg-transparent bg-none text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            <option value="">All Divisions</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div }}" {{ request('division') == $div ? 'selected' : '' }}>{{ $div }}</option>
                            @endforeach
                        </select>

                        @if(request()->anyFilled(['search', 'position', 'division', 'partnership']))
                            <a href="{{ route('employees.index') }}" class="text-sm text-red-500 hover:text-red-700">Clear</a>
                        @endif
                    </form>

                    @can('manage', App\Models\Employee::class)
                    <button @click="openCreate()"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Employee
                    </button>
                    @endcan
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="max-w-full overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-start">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'nik', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 text-theme-xs font-medium text-gray-500 hover:text-blue-600">
                                NIK
                                <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" stroke-width="2"/></svg>
                            </a>
                        </th>
                        <th class="px-6 py-3 text-start">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'first_name', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 text-theme-xs font-medium text-gray-500 hover:text-blue-600">
                                Name
                                <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" stroke-width="2"/></svg>
                            </a>
                        </th>
                        <th class="px-6 py-3 text-start text-theme-xs font-medium text-gray-500">Division</th>
                        <th class="px-6 py-3 text-start text-theme-xs font-medium text-gray-500">Teams</th>
                        <th class="px-6 py-3 text-start text-theme-xs font-medium text-gray-500">Manager</th>
                        <th class="px-6 py-3 text-start">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'date_of_entry', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 text-theme-xs font-medium text-gray-500 hover:text-blue-600">
                                Entry Date
                                <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" stroke-width="2"/></svg>
                            </a>
                        </th>
                        <th class="px-6 py-3 text-start text-theme-xs font-medium text-gray-500">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="row in tableRowData" :key="row.id">
                        <tr class="border-b border-gray-100 dark:border-white/[0.05] hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="px-6 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400" x-text="row.nik"></td>
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-medium"
                                         :class="[row.avatarBg, row.avatarColor]">
                                        <span x-text="row.initials"></span>
                                    </div>
                                    <div>
                                        <span class="block text-theme-sm font-medium text-gray-700 dark:text-gray-400" x-text="row.employeeName"></span>
                                        <span class="text-xs text-gray-500" x-text="row.position"></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400" x-text="row.division"></td>
                            <td class="px-6 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400" x-text="row.partnership"></td>
                            <td class="px-6 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400" x-text="row.manager"></td>
                            <td class="px-6 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400" x-text="row.entry_date"></td>
                            <td class="px-6 py-3.5">
                                @can('manage', App\Models\Employee::class)
                                <div class="flex items-center gap-2">
                                    <!-- Edit button -->
                                    <button @click="openEdit(row)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition-colors"
                                        title="Edit">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <!-- Delete button -->
                                    <button @click="deleteRow(row.id, row.employeeName)"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors"
                                        title="Delete">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                                @endcan
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Footer / Pagination -->
        <div class="flex flex-col items-center justify-between gap-4 px-6 py-4 border-t border-gray-100 sm:flex-row dark:border-white/[0.05]">
            <div class="flex items-center gap-2">
                <p class="text-sm text-gray-500 dark:text-gray-400">Show</p>
                <select onchange="window.location.href = this.value"
                    class="block px-3 py-1.5 text-sm border border-gray-200 rounded-lg dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 bg-transparent bg-none text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                    @foreach([5, 10, 20, 25] as $size)
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => $size]) }}"
                            {{ request('per_page') == $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
                <p class="text-sm text-gray-500 dark:text-gray-400">entries</p>
            </div>
            <div class="pagination-links text-sm text-gray-500 dark:text-gray-400">
                {{ $employees->links() }}
            </div>
        </div>
    </div>
</div>
@endsection