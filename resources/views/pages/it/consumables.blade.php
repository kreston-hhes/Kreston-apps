@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Stok Tinta & Consumables" />

    @if (session('success'))
        <div class="mb-6 p-4 rounded-xl bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400 border border-green-200 dark:border-green-800">
            {{ session('success') }}
        </div>
    @endif
    
    <div class="w-full space-y-6" x-data="{ 
        stockCyan: {{ $stokCyan ?? 0 }}, 
        stockMagenta: {{ $stokMagenta ?? 0 }}, 
        stockYellow: {{ $stokYellow ?? 0 }}, 
        stockBlack: {{ $stokBlack ?? 0 }},
        
        // Modal khusus untuk bagian Tools
        isToolModalOpen: false, 
        toolModalType: '' 
    }">
        
        <!-- HEADER KETERANGAN PRINTER -->
        <div class="p-4 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg dark:bg-blue-900/20 dark:text-blue-400">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Centralized Printer Supplies</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Stok di bawah ini digunakan bersama (shared) untuk 4 unit printer CMYK yang ada di kantor.</p>
                </div>
            </div>
        </div>

        <!-- GRID STOK CMYK DENGAN TOMBOL PLUS MINUS -->
        <h2 class="text-lg font-bold text-gray-800 dark:text-white mt-6">Toner CMYK</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- CYAN -->
            <div class="relative p-5 bg-white border-t-4 border-t-cyan-500 rounded-xl shadow-sm dark:bg-gray-800 border-x border-b border-x-gray-100 border-b-gray-100 dark:border-x-gray-700 dark:border-b-gray-700 flex flex-col items-center overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-b from-cyan-500/5 to-transparent pointer-events-none hidden dark:block"></div>
                <h3 class="relative text-sm font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cyan (C)</h3>
                <div class="relative mt-4 flex items-center justify-between w-full max-w-[140px]">
                    <button @click="if(stockCyan > 0) stockCyan--" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg transition-colors border border-gray-300 bg-white shadow-sm text-gray-700 hover:bg-red-50 hover:text-red-500 hover:border-red-300 dark:bg-gray-900 dark:text-gray-400 dark:border-gray-700 dark:hover:bg-red-500/20 dark:hover:text-red-400 dark:hover:border-red-500/30">
                        -
                    </button>
                    <div class="flex flex-col items-center">
                        <span class="text-3xl font-black text-gray-900 dark:text-white" x-text="stockCyan"></span>
                        <span class="text-[10px] text-gray-400 font-medium uppercase mt-1">Box</span>
                    </div>
                    <button @click="stockCyan++" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg transition-colors border-2 border-cyan-500 bg-cyan-500 text-white hover:bg-cyan-600 shadow-md shadow-cyan-500/20 dark:shadow-none">
                        +
                    </button>
                </div>
            </div>

            <!-- MAGENTA -->
            <div class="relative p-5 bg-white border-t-4 border-t-pink-500 rounded-xl shadow-sm dark:bg-gray-800 border-x border-b border-x-gray-100 border-b-gray-100 dark:border-x-gray-700 dark:border-b-gray-700 flex flex-col items-center overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-b from-pink-500/5 to-transparent pointer-events-none hidden dark:block"></div>
                <h3 class="relative text-sm font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Magenta (M)</h3>
                <div class="relative mt-4 flex items-center justify-between w-full max-w-[140px]">
                    <button @click="if(stockMagenta > 0) stockMagenta--" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg transition-colors border border-gray-300 bg-white shadow-sm text-gray-700 hover:bg-red-50 hover:text-red-500 hover:border-red-300 dark:bg-gray-900 dark:text-gray-400 dark:border-gray-700 dark:hover:bg-red-500/20 dark:hover:text-red-400 dark:hover:border-red-500/30">
                        -
                    </button>
                    <div class="flex flex-col items-center">
                        <span class="text-3xl font-black text-gray-900 dark:text-white" x-text="stockMagenta"></span>
                        <span class="text-[10px] text-gray-400 font-medium uppercase mt-1">Box</span>
                    </div>
                    <button @click="stockMagenta++" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg transition-colors border-2 border-pink-500 bg-pink-500 text-white hover:bg-pink-600 shadow-md shadow-pink-500/20 dark:shadow-none">
                        +
                    </button>
                </div>
            </div>

            <!-- YELLOW -->
            <div class="relative p-5 bg-white border-t-4 border-t-yellow-400 rounded-xl shadow-sm dark:bg-gray-800 border-x border-b border-x-gray-100 border-b-gray-100 dark:border-x-gray-700 dark:border-b-gray-700 flex flex-col items-center overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-b from-yellow-400/5 to-transparent pointer-events-none hidden dark:block"></div>
                <h3 class="relative text-sm font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Yellow (Y)</h3>
                <div class="relative mt-4 flex items-center justify-between w-full max-w-[140px]">
                    <button @click="if(stockYellow > 0) stockYellow--" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg transition-colors border border-gray-300 bg-white shadow-sm text-gray-700 hover:bg-red-50 hover:text-red-500 hover:border-red-300 dark:bg-gray-900 dark:text-gray-400 dark:border-gray-700 dark:hover:bg-red-500/20 dark:hover:text-red-400 dark:hover:border-red-500/30">
                        -
                    </button>
                    <div class="flex flex-col items-center">
                        <span class="text-3xl font-black text-gray-900 dark:text-white" x-text="stockYellow"></span>
                        <span class="text-[10px] text-gray-400 font-medium uppercase mt-1">Box</span>
                    </div>
                    <button @click="stockYellow++" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg transition-colors border-2 border-yellow-400 bg-yellow-400 text-yellow-900 hover:bg-yellow-500 shadow-md shadow-yellow-400/20 dark:shadow-none">
                        +
                    </button>
                </div>
            </div>

            <!-- BLACK -->
            <div class="relative p-5 bg-white border-t-4 border-t-gray-700 rounded-xl shadow-sm dark:bg-gray-800 border-x border-b border-x-gray-100 border-b-gray-100 dark:border-x-gray-700 dark:border-b-gray-700 flex flex-col items-center overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-b from-gray-500/5 to-transparent pointer-events-none hidden dark:block"></div>
                <h3 class="relative text-sm font-bold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Key / Black (K)</h3>
                <div class="relative mt-4 flex items-center justify-between w-full max-w-[140px]">
                    <button @click="if(stockBlack > 0) stockBlack--" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg transition-colors border border-gray-300 bg-white shadow-sm text-gray-700 hover:bg-red-50 hover:text-red-500 hover:border-red-300 dark:bg-gray-900 dark:text-gray-400 dark:border-gray-700 dark:hover:bg-red-500/20 dark:hover:text-red-400 dark:hover:border-red-500/30">
                        -
                    </button>
                    <div class="flex flex-col items-center">
                        <span class="text-3xl font-black text-gray-900 dark:text-white" x-text="stockBlack"></span>
                        <span class="text-[10px] text-gray-400 font-medium uppercase mt-1">Box</span>
                    </div>
                    <button @click="stockBlack++" class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg transition-colors border-2 border-gray-700 bg-gray-800 text-white hover:bg-gray-900 shadow-md shadow-gray-900/20 dark:shadow-none dark:border-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600">
                        +
                    </button>
                </div>
            </div>
        </div>

        <!-- SECTION TOOLS -->
        <div class="flex items-center justify-between mt-8 mb-4">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white">Tools & Maintenance Kit</h2>
            <button @click="isToolModalOpen = true; toolModalType = 'Tool Baru'" class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                + Tambah Tool Baru
            </button>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-2xl dark:bg-white/[0.03] dark:border-white/[0.05] overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Nama Tool</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Stok Tersedia</th>
                        <th class="px-6 py-3 text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/[0.05]">
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">Waste Toner Box</td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">2 Pcs</td>
                        <td class="px-6 py-4">
                            <button @click="isToolModalOpen = true; toolModalType = 'Waste Toner Box'" class="text-xs text-blue-600 hover:underline dark:text-blue-400">Update Stok</button>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">Drum Unit</td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">1 Pcs</td>
                        <td class="px-6 py-4">
                            <button @click="isToolModalOpen = true; toolModalType = 'Drum Unit'" class="text-xs text-blue-600 hover:underline dark:text-blue-400">Update Stok</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- MODAL UPDATE STOK (Hanya disisakan untuk Tools) -->
        <div x-show="isToolModalOpen" x-cloak class="fixed inset-0 z-[9999] overflow-y-auto p-4 pt-10 sm:p-8 sm:pt-16">
            <div x-show="isToolModalOpen" class="fixed inset-0 bg-black/40" @click="isToolModalOpen = false"></div>
            <div x-show="isToolModalOpen" class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md mx-auto p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Update Stok: <span x-text="toolModalType" class="text-blue-600"></span></h3>
                <form action="#" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Jenis Transaksi</label>
                        <select name="type" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            <option value="in">Stok Masuk (+)</option>
                            <option value="out">Stok Keluar / Dipakai (-)</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Jumlah (Qty)</label>
                        <input type="number" min="1" name="quantity" required class="w-full px-3 py-2 border rounded-lg dark:bg-gray-900 dark:border-gray-700 dark:text-white" placeholder="Contoh: 1">
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="isToolModalOpen = false" class="px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200">Batal</button>
                        <button type="submit" class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection