@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Asset IT Management" />

    <div
        class="min-h-screen rounded-2xl border border-gray-200 bg-white px-5 py-7 dark:border-gray-800 dark:bg-white/[0.03] xl:px-10 xl:py-12">

        @if (session('success'))
            <div
                class="mb-6 p-4 rounded-xl bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400 border border-green-200 dark:border-green-800">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div
                class="mb-6 p-4 rounded-xl bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400 border border-red-200 dark:border-red-800">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Daftar Aset IT Kantor</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Kelola dan pantau seluruh perangkat keras, lunak, dan
                    jaringan per-partner.</p>
            </div>
            <button @click="$dispatch('open-modal', 'modal-add-asset')"
                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition">
                + Tambah Aset Baru
            </button>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
            <div class="overflow-x-auto">
                <table
                    class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-left text-sm text-gray-600 dark:text-gray-300">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-200 font-medium">
                        <tr>
                            <th class="px-6 py-4">Hostname / Code</th>
                            <th class="px-6 py-4">Nama Perangkat</th>
                            <th class="px-6 py-4">Partner</th>
                            <th class="px-6 py-4">Kategori / Jenis</th>
                            <th class="px-6 py-4">Kondisi</th>
                            <th class="px-6 py-4">Status Posisi & Pengguna</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-transparent">
                        @forelse($assets as $asset)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition">
                                <td class="px-6 py-4 font-mono font-semibold text-blue-600 dark:text-blue-400">
                                    {{ $asset->asset_code }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $asset->name }}</div>
                                    <div class="text-xs text-gray-400 font-mono">SN: {{ $asset->serial_number ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4"><span
                                        class="px-2.5 py-1 text-xs font-medium rounded-md bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-900/20 dark:text-purple-400 dark:border-purple-800">{{ $asset->partnership->name }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-gray-900 dark:text-white font-medium">{{ $asset->type->name }}</span>
                                    <div class="text-xs text-gray-400">{{ $asset->type->category->name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($asset->condition_status == 'good')
                                        <span class="text-green-600 dark:text-green-400 font-medium">Bagus</span>
                                    @elseif($asset->condition_status == 'maintenance')
                                        <span class="text-yellow-600 dark:text-yellow-400 font-medium">Perbaikan</span>
                                    @else
                                        <span class="text-red-600 dark:text-red-400 font-medium">Rusak</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($asset->placement_status == 'it_room')
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-gray-500"></span> Di Ruang IT
                                        </span>
                                    @else
                                        <div class="flex flex-col">
                                            <span
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400 max-w-max mb-1">
                                                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> Dipakai Karyawan
                                            </span>
                                            <span
                                                class="text-xs font-medium text-gray-900 dark:text-white">{{ $asset->currentAssignment->employee->first_name ?? 'Karyawan' }}</span>
                                            <span
                                                class="text-xs text-gray-400">{{ $asset->currentAssignment->employee->user->email ?? '' }}</span>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">Belum ada
                                    aset IT yang terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <x-modal name="modal-add-asset" title="Tambah Aset IT Baru">

        <form action="{{ route('assets.store') }}" method="POST">
            @csrf
            <div class="space-y-4 text-left"> {{-- Tambahkan text-left agar text form rapi --}}

                <div>

                    <x-form.select-input name="partnership_id" label="Partnership" create="false" direction="asc" required
                        requiredMessage="Choose a partnership first">

                        <option value="">--Select--</option>

                        @foreach (\App\Models\Partnership::all() as $partner)
                            <option value="{{ $partner->id }}">{{ $partner->name }} ({{ $partner->code }})</option>
                        @endforeach

                    </x-form.select-input>

                </div>

                <div>


                    <x-form.select-input name="type_id" label="Asset Type" create="false" direction="asc" required
                        requiredMessage="Choose a Asset Type">

                        <option value="">--Select--</option>

                        @foreach (\App\Models\AssetType::with('category')->get() as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}
                                ({{ $type->type_code }})
                            </option>
                        @endforeach

                    </x-form.select-input>

                </div>

                <div>

                    <x-form.text-input name="name" label="Asset Model" placeholder="Example: ThinkPad X1 Carbon Gen 11"
                        required requiredMessage="Please fill in the asset model" />
                </div>

                <div>

                    <x-form.text-input name="serial_number" label="Serial Number" placeholder="Empty if not available" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-form.date-picker name="purchase_date" label="Purchase Date" altInput="true" altFormat="d-m-Y"
                            required />

                    </div>
                    <div>

                        <x-form.date-picker name="warranty_expired" label="Warranty Expired" altInput="true"
                            altFormat="d-m-Y" required />
                    </div>
                </div>

                <div>
                    <x-form.text-area-input name="specification" label="Detail Spesification"
                        placeholder="Example: Core i7, RAM 16GB, SSD 512GB" required />

                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-6 border-t border-gray-200 dark:border-gray-800 pt-4">
                <button type="button" @click="open = false"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                    Batal
                </button>
                <button type="submit"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Simpan Aset
                </button>
            </div>
        </form>

    </x-modal>
@endsection
