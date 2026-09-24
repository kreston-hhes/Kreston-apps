@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title ?? 'Logbook Peminjaman'" />

    @if (session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 text-green-700 border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <!-- STATUS KETERSEDIAAN PROYEKTOR -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        @foreach($assets as $asset)
            <div class="flex items-center justify-between p-4 rounded-xl border transition-colors {{ $asset->is_available ? 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800' : 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800' }}">
                <div class="flex items-center gap-4">
                    <div class="p-2.5 rounded-lg {{ $asset->is_available ? 'bg-green-100 text-green-600 dark:bg-green-800 dark:text-green-300' : 'bg-red-100 text-red-600 dark:bg-red-800 dark:text-red-300' }}">
                        <!-- Ikon Proyektor -->
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm {{ $asset->is_available ? 'text-green-800 dark:text-green-400' : 'text-red-800 dark:text-red-400' }}">{{ $asset->name }}</h3>
                        <p class="text-xs {{ $asset->is_available ? 'text-green-600 dark:text-green-500' : 'text-red-600 dark:text-red-500' }}">{{ $asset->asset_code }}</p>
                    </div>
                </div>
                <div>
                    <span class="px-3 py-1.5 text-xs font-semibold rounded-full shadow-sm {{ $asset->is_available ? 'bg-green-100 text-green-700 border border-green-200 dark:bg-green-800 dark:text-green-100 dark:border-green-700' : 'bg-red-100 text-red-700 border border-red-200 dark:bg-red-800 dark:text-red-100 dark:border-red-700' }}">
                        {{ $asset->is_available ? 'Tersedia' : 'Dipinjam' }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- WRAPPER ALPINE.JS -->
    <div class="w-full space-y-6 relative" x-data="{ activeTab: 'history' }">
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
            
            <!-- Tabs -->
            <div class="flex items-center gap-1 px-6 pt-5 border-b border-gray-100 dark:border-gray-700">
                <button @click="activeTab = 'history'"
                    :class="activeTab === 'history' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                    class="pb-3 px-4 text-sm transition-colors">
                    Riwayat Peminjaman
                </button>
                <button @click="activeTab = 'create'"
                    :class="activeTab === 'create' ? 'border-b-2 border-blue-600 text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                    class="pb-3 px-4 text-sm transition-colors">
                    + Catat Peminjaman Baru
                </button>
            </div>

            <!-- TAB 1: RIWAYAT PEMINJAMAN -->
            <div x-show="activeTab === 'history'">
                <div class="max-w-full overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300 mt-4">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-6 py-3 font-medium text-gray-500">Aset</th>
                                <th class="px-6 py-3 font-medium text-gray-500">PIC & Klien</th>
                                <th class="px-6 py-3 font-medium text-gray-500">Waktu Pinjam</th>
                                <!-- Kolom Baru: Durasi / Catatan -->
                                <th class="px-6 py-3 font-medium text-gray-500">Durasi / Catatan</th>
                                <th class="px-6 py-3 font-medium text-gray-500">Waktu Kembali</th>
                                <th class="px-6 py-3 font-medium text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($loans as $loan)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $loan->asset->name ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ $loan->pic_name }}</span><br>
                                        <span class="text-xs text-gray-500">{{ $loan->partnership->name ?? '-' }}</span>
                                    </td>
                                    <td class="px-6 py-4">{{ \Carbon\Carbon::parse($loan->borrowed_at)->format('d M Y H:i') }}</td>
                                    <!-- Menampilkan Data Catatan Durasi -->
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $loan->duration_note ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        @if($loan->returned_at)
                                            <span class="text-green-600 font-medium">{{ \Carbon\Carbon::parse($loan->returned_at)->format('d M Y H:i') }}</span>
                                        @else
                                            <span class="text-orange-500 font-medium">Masih Dipinjam</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if(!$loan->returned_at)
                                            <form action="{{ route('loans.finish', $loan->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" onclick="return confirm('Tandai proyektor ini sudah dikembalikan?')" class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg">
                                                    Selesai
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <!-- Colspan diubah menjadi 6 karena ada tambahan kolom -->
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada data peminjaman.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: BUAT PEMINJAMAN BARU -->
            <div x-show="activeTab === 'create'" x-cloak class="p-6">
                <form action="{{ route('loans.store') }}" method="POST">
                    @csrf
                    <div class="bg-gray-50 dark:bg-gray-900/50 p-6 rounded-xl border border-gray-100 dark:border-gray-700 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Aset (Proyektor)</label>
                                <select name="asset_id" required class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-white">
                                    <option value="">Pilih Aset...</option>
                                    @foreach($assets as $asset)
                                        <option value="{{ $asset->id }}">{{ $asset->name }} ({{ $asset->asset_code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tim / Klien (Partnership)</label>
                                <select name="partnership_id" required class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-white">
                                    <option value="">Pilih Tim/Klien...</option>
                                    @foreach($partnerships as $partner)
                                        <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama PIC (Peminjam)</label>
                                <input type="text" name="pic_name" required placeholder="Contoh: Budi Santoso" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Catatan Durasi</label>
                                <input type="text" name="duration_note" required placeholder="Contoh: Dipinjam dari pagi sampai jam 3 sore" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Simpan Peminjaman
                        </button>
                    </div>
                </form>
            </div>
            
        </div>
    </div>
@endsection