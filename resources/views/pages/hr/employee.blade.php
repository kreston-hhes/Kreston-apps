@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="Employee" />

<div class="w-full space-y-6" x-data="{
    tableRowData: @js($tableData),
    deleteRow(id, name) {
        if (confirm(`Are you sure you want to delete employee: ${name}?`)) {
            fetch(`/employees/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.tableRowData = this.tableRowData.filter(row => row.id !== id);
                    const successNotification = {
                        variant: 'success',
                        title: 'Delete Berhasil',
                        message: `${name} deleted successfully!`
                    };
                    if (window.showNotification) {
                        window.showNotification(successNotification);
                    } else {
                        alert(successNotification.message);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal menghapus data.');
            });
        }
    }
}">
    <div class="bg-white border border-gray-200 rounded-2xl dark:bg-white/[0.03] dark:border-white/[0.05]">
        
        <!-- Header & Filter Section -->
        <div class="p-6 border-b border-gray-100 dark:border-white/[0.05]">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Employee List</h3>
                
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Global Search Form -->
                    <form action="{{ route('employees.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                        <div class="relative w-full sm:w-64">
                            <span class="absolute -translate-y-1/2 left-3 top-1/2 text-gray-400">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2"/></svg>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search NIK or Name..." 
                                class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-900 dark:border-white/[0.05]">
                        </div>

                        <select name="position" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-lg text-sm dark:bg-gray-900 dark:border-white/[0.05]">
                            <option value="">All Positions</option>
                            @foreach($positions as $pos)
                                <option value="{{ $pos }}" {{ request('position') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
                            @endforeach
                        </select>

                                 <select name="partnership" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-lg text-sm dark:bg-gray-900 dark:border-white/[0.05]">
                            <option value="">All Teams</option>
                            @foreach($partnerships as $partnership)
                                <option value="{{ $partnership }}" {{ request('partnership') == $partnership ? 'selected' : '' }}>{{ $partnership }}</option>
                            @endforeach
                        </select>

                        <select name="division" onchange="this.form.submit()" class="px-3 py-2 border border-gray-200 rounded-lg text-sm dark:bg-gray-900 dark:border-white/[0.05]">
                            <option value="">All Divisions</option>
                            @foreach($divisions as $div)
                                <option value="{{ $div }}" {{ request('division') == $div ? 'selected' : '' }}>{{ $div }}</option>
                            @endforeach
                        </select>

                        @if(request()->anyFilled(['search', 'position', 'division']))
                            <a href="{{ route('employees.index') }}" class="text-sm text-red-500 hover:text-red-700">Clear</a>
                        @endif
                    </form>
@can('manage', App\Models\Employee::class)
                    <button class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Add New
                    </button>
@endcan
                </div>
            </div>
        </div>

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
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-medium" :class="[row.avatarBg, row.avatarColor]">
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
                                <button @click="deleteRow(row.id, row.employeeName)" class="text-gray-500 hover:text-red-500">
                                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
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
                <select onchange="window.location.href = this.value" class="block px-3 py-1.5 text-sm border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-white/[0.05]">
                    @foreach([5, 10, 20, 25] as $size)
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => $size]) }}" {{ request('per_page') == $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
                <p class="text-sm text-gray-500 dark:text-gray-400">entries</p>
            </div>
            <div class="pagination-links">
                {{ $employees->links() }}
            </div>
        </div>
    </div>
</div>
@endsection