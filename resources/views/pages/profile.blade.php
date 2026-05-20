@extends('layouts.app')

@section('content')
<x-common.page-breadcrumb pageTitle="User Profile" />

<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6" 
     x-data="{ 
        updateProfile() { console.log('Updating profile...'); },
        saveProfile() { this.$refs.profileForm.submit(); }
     }">
    
    <h3 class="mb-5 text-lg font-semibold text-gray-800 dark:text-white/90 lg:mb-7">Profile</h3>
    
    <!-- Personal Information Card -->
    <div class="p-5 mb-6 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex-1">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 lg:mb-6">Personal Information</h4>
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
                    <div>
                        <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">First Name</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $employee->first_name }}</p>
                    </div>

                    <div>
                        <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Last Name</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $employee->last_name }}</p>
                    </div>

                    <div>
                        <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Email address</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $employee->email }}</p>
                    </div>

                    <div>
                        <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Phone</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $employee->phone }}</p>
                    </div>

                    <div>
                        <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Address</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $employee->address }}</p>
                    </div>

                    <div>
                        <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Gender</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ ucfirst($employee->gender) }}</p>
                    </div>

                    <div>
                        <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Date of Birth</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $employee->birth_date ? date('d F Y', strtotime($employee->birth_date)) : '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 lg:gap-4 shrink-0">   
                <button class="edit-button flex items-center gap-2 px-4 py-2 border rounded-lg text-sm font-medium" @click="$dispatch('open-profile-address-modal')">
                    <svg class="fill-current" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M15.0911 2.78206C14.2125 1.90338 12.7878 1.90338 11.9092 2.78206L4.57524 10.116C4.26682 10.4244 4.0547 10.8158 3.96468 11.2426L3.31231 14.3352C3.25997 14.5833 3.33653 14.841 3.51583 15.0203C3.69512 15.1996 3.95286 15.2761 4.20096 15.2238L7.29355 14.5714C7.72031 14.4814 8.11172 14.2693 8.42013 13.9609L15.7541 6.62695C16.6327 5.74827 16.6327 4.32365 15.7541 3.44497L15.0911 2.78206ZM12.9698 3.84272C13.2627 3.54982 13.7376 3.54982 14.0305 3.84272L14.6934 4.50563C14.9863 4.79852 14.9863 5.2734 14.6934 5.56629L14.044 6.21573L12.3204 4.49215L12.9698 3.84272ZM11.2597 5.55281L5.6359 11.1766C5.53309 11.2794 5.46238 11.4099 5.43238 11.5522L5.01758 13.5185L6.98394 13.1037C7.1262 13.0737 7.25666 13.003 7.35947 12.9002L12.9833 7.27639L11.2597 5.55281Z" />
                    </svg>
                    Edit Profile
                </button>
            </div>
        </div>
    </div>

    <!-- Company Information Card -->
    <div class="p-5 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex-1">
                <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 lg:mb-6">Company Information</h4>
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
                    <div>
                        <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">NIK</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $employee->nik }}</p>
                    </div>

                    <div>
                        <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Position</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $employee->position }}</p>
                    </div>

                    <div>
                        <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Division</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $employee->division }}</p>
                    </div>

                    <div>
                        <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Manager</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $employee->manager ? trim($employee->manager->first_name . ' ' . $employee->manager->last_name) : 'No Manager' }}
                        </p>
                    </div>

                    <div>
                        <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Team Partner</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                            {{ $employee->partnership ? $employee->partnership->name : 'No Partner' }}
                        </p>
                    </div>

                    <div>
                        <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Entry Date</p>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $employee->date_of_entry ? date('d F Y', strtotime($employee->date_of_entry)) : '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Component -->
    <x-ui.modal @open-profile-address-modal.window="open = true" :isOpen="false" class="max-w-[700px]">
        <!-- PERBAIKAN CSS MODAL: Tambahkan overflow-visible atau h-screen/min-h untuk ruang dropdown picker -->
        <div class="no-scrollbar relative w-full max-w-[700px] rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11 overflow-visible">
            <div class="px-2 pr-14">
                <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">Edit Profile</h4>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">
                    Update your profile information and address details to ensure your account is up to date.
                </p>
            </div>
            
            <!-- PERBAIKAN DATABASE: Tambah properti action, method, CSRF, dan Route PUT -->
            <form action="{{ route('profile.update') }}" method="POST" x-ref="profileForm" class="flex flex-col">
                @csrf
                @method('PUT')

                <div class="px-2">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                        <!-- PERBAIKAN DATABASE: value dihubungkan ke variabel $employee dan atribut name ditambah -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">First Name</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $employee->first_name) }}"
                                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Last Name</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}"
                                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}"
                                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Address</label>
                            <input type="text" name="address" value="{{ old('address', $employee->address) }}"
                                class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" />
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Gender</label>
                            <div x-data="{ isOptionSelected: {{ $employee->gender ? 'true' : 'false' }} }" class="relative z-20 bg-transparent">
                                <select name="gender"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                    :class="isOptionSelected && 'text-gray-800 dark:text-white/90'" @change="isOptionSelected = true">
                                  
                                    <option value="female" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400" {{ $employee->gender == 'female' ? 'selected' : '' }}>
                                        Female
                                    </option>
                                    <option value="male" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400" {{ $employee->gender == 'male' ? 'selected' : '' }}>
                                        Male
                                    </option>
                                </select>
                                <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                                    <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Date of Birth</label>
    
    <!-- PENTING: Menyisipkan CSS Flatpickr agar kalender melayang dengan benar -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <div x-data="{
        flatpickrInstance: null,
        init() {
            this.$nextTick(() => {
                this.flatpickrInstance = flatpickr(this.$refs.dateInput, {
                    mode: 'single',
                    static: false, 
                    monthSelectorType: 'static',
                    
                    // Mengubah tampilan di input menjadi dd-mm-yyyy
                    altInput: true,
                    altFormat: 'd-m-Y',
                    
                    // Format asli yang dikirim ke database Laravel (yyyy-mm-dd)
                    dateFormat: 'Y-m-d',
                    
                    defaultDate: '{{ $employee->birth_date }}',
                    
                    // Mencegah konflik style dengan Tailwind di dalam modal
                    onChange: (selectedDates, dateStr, instance) => {
                        this.$dispatch('date-change', { selectedDates, dateStr, instance });
                    }
                });
            });
        },
        destroy() {
            if (this.flatpickrInstance) {
                this.flatpickrInstance.destroy();
            }
        }
    }" x-init="init()" x-destroy="destroy()" class="relative custom-datepicker">
        
        <input
            x-ref="dateInput"
            type="text"
            name="birth_date"
            placeholder="Select date"
            class="h-11 w-full rounded-lg border appearance-none px-4 py-2.5 text-sm shadow-theme-xs placeholder:text-gray-400 focus:outline-hidden focus:ring-3 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 bg-transparent text-gray-800 border-gray-300 focus:border-brand-300 focus:ring-brand-500/20 dark:border-gray-700 dark:focus:border-brand-800"
            autocomplete="off"
        />
        
        <span class="absolute text-gray-500 -translate-y-1/2 pointer-events-none right-3 top-1/2 dark:text-gray-400 z-50">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="none" class="size-6">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M8 2C8.41421 2 8.75 2.33579 8.75 2.75V3.75H15.25V2.75C15.25 2.33579 15.5858 2 16 2C16.4142 2 16.75 2.33579 16.75 2.75V3.75H18.5C19.7426 3.75 20.75 4.75736 20.75 6V9V19C20.75 20.2426 19.7426 21.25 18.5 21.25H5.5C4.25736 21.25 3.25 20.2426 3.25 19V9V6C3.25 4.75736 4.25736 3.75 5.5 3.75H7.25V2.75C7.25 2.33579 7.58579 2 8 2ZM8 5.25H5.5C5.08579 5.25 4.75 5.58579 4.75 6V8.25H19.25V6C19.25 5.58579 18.9142 5.25 18.5 5.25H16H8ZM19.25 9.75H4.75V19C4.75 19.4142 5.08579 19.75 5.5 19.75H18.5C18.9142 19.75 19.25 19.4142 19.25 19V9.75Z" fill="currentColor"></path>
            </svg>
        </span>
    </div>
</div>

                    </div>
                </div>
                
                <div class="flex items-center gap-3 mt-6 lg:justify-end">
                    <button @click="open = false" type="button"
                        class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] sm:w-auto">
                        Close
                    </button>
                    <!-- PERBAIKAN: Type button memanggil fungsi saveProfile() Alpine untuk submit -->
                    <button @click="saveProfile()" type="button"
                        class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </x-ui.modal>

</div>
@endsection