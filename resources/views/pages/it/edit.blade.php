@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Aset IT: {{ $asset->asset_code }}" />

    <div class="max-w-4xl mx-auto mt-6 bg-white border border-gray-200 rounded-2xl dark:bg-white/[0.03] dark:border-white/[0.05] p-6 sm:p-8">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Edit Data Aset: <span class="text-blue-500">{{ $asset->asset_code }}</span></h2>
        
        <!-- Action mengarah ke fungsi Update. Perhatikan x-data kini dibungkus kutip tunggal (') -->
        <form action="{{ route('assets.update', $asset->id) }}" method="POST" x-data='{
            selectedCategory: `{{ strtolower($asset->type->name ?? "") }}`,
            purchaseDate: `{{ $asset->purchase_date }}`,
            warrantyExpired: `{{ $asset->warranty_expired }}`, 
            
            deviceName: `{{ $asset->name }}`,
            
            // Mengambil JSON spesifikasi dari database dan mengurai ke bentuk array untuk form
            specs: (() => {
                let savedSpecs = @json($asset->specification ?? []);
                let arr = [];
                for (let k in savedSpecs) {
                    arr.push({ key: k, value: savedSpecs[k] });
                }
                return arr.length > 0 ? arr : [
                    { key: `Merk`, value: `` }, { key: `Tipe`, value: `` }
                ];
            })(),

            get formattedSpecs() {
                let specObject = {};
                this.specs.filter(s => s.key && s.value).forEach(s => {
                    specObject[s.key] = s.value;
                });
                return JSON.stringify(specObject);
            },
            
            handleCategoryChange(e) {
                let catName = e.target.options[e.target.selectedIndex].text.toLowerCase();
                this.selectedCategory = catName;
                
                if (catName.includes(`laptop`)) {
                    this.specs = [
                        { key: `Merk`, value: `` }, { key: `Tipe`, value: `` },
                        { key: `Processor`, value: `` }, { key: `RAM`, value: `` },
                        { key: `Storage`, value: `` }, { key: `OS`, value: `` }
                    ];
                } else if (catName.includes(`printer`)) {
                    this.specs = [
                        { key: `Merk`, value: `` }, { key: `Tipe`, value: `` },
                        { key: `Jenis warna`, value: `` }, { key: `IP address`, value: `` }
                    ];
                } else {
                    this.specs = [{ key: `Merk`, value: `` }, { key: `Tipe`, value: `` }];
                }
            }
        }'>
            @csrf
            @method('PUT')
            
            <input type="hidden" name="specification" :value="formattedSpecs">

            <div class="space-y-4 text-left">
                <!-- Baris 1: Kategori & Partner -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Kategori aset</label>
                        <select name="type_id" @change="handleCategoryChange($event)" required class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            @foreach (\App\Models\AssetType::all()->unique(fn($item) => strtolower(trim($item->name))) as $type)
                                <option value="{{ $type->id }}" {{ $asset->type_id == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} ({{ $type->type_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Partner/tim</label>
                        <select name="partnership_id" class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            <option value="">Pilih tim</option>
                            @foreach (\App\Models\Partnership::all() as $partner)
                                <option value="{{ $partner->id }}" {{ $asset->partnership_id == $partner->id ? 'selected' : '' }}>
                                    {{ $partner->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Baris 2: Nama & SN -->
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Nama perangkat</label>
                    <input type="text" name="name" required placeholder="Nama aset" x-model="deviceName" 
                        class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:placeholder-gray-400">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Kode Aset</label>
                        <!-- Saat edit, kode aset dibuat readonly karena tidak boleh diubah sembarangan -->
                        <input type="text" class="w-full bg-gray-100 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-md px-3 py-2 text-gray-500 dark:text-gray-400 text-sm cursor-not-allowed" value="{{ $asset->asset_code }}" readonly disabled>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Serial Number</label>
                        <input type="text" name="serial_number" value="{{ $asset->serial_number }}" class="w-full bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-md px-3 py-2 text-gray-900 dark:text-white text-sm focus:outline-none focus:border-blue-500" placeholder="Masukkan Serial Number">
                    </div>
                </div>

                <!-- Spesifikasi Dinamis -->
                <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg dark:bg-white/[0.02] dark:border-gray-700">
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
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Bagian Bawah -->
                <div x-show="!selectedCategory.includes('printer')" x-cloak class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Vendor</label>
                            <input type="text" name="vendor" value="{{ $asset->vendor }}" placeholder="Nama vendor" class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:placeholder-gray-400">
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Dibeli oleh</label>
                            <input type="text" name="purchased_by" value="{{ $asset->purchased_by }}" placeholder="Nama/tim" class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:placeholder-gray-400">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Pembelian</label>
                            <input type="text" name="purchase_date" x-model="purchaseDate" 
                                x-init="flatpickr($el, { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd-m-Y', defaultDate: '{{ $asset->purchase_date }}' })"
                                class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <!-- Untuk Edit, input garansi dibuat langsung berupa Date Picker agar lebih leluasa -->
                            <label class="block mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Garansi Berakhir</label>
                            <input type="text" name="warranty_expired" x-model="warrantyExpired" 
                                x-init="flatpickr($el, { dateFormat: 'Y-m-d', altInput: true, altFormat: 'd-m-Y', defaultDate: '{{ $asset->warranty_expired }}' })"
                                class="w-full px-3 py-2 text-sm bg-white border border-gray-300 rounded-lg focus:outline-none focus:border-gray-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-3 mt-8 pt-5 border-t border-gray-100 dark:border-gray-700">
                <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white transition-colors duration-200 bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Simpan Perubahan
                </button>
                <a href="{{ route('assets.index') }}" class="px-6 py-2.5 text-sm font-semibold text-gray-700 transition-colors duration-200 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection