<div class="relative" x-data="{
    dropdownOpen: false,
    toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
    },
    closeDropdown() {
        this.dropdownOpen = false;
    }
}" @click.away="closeDropdown()">
    <!-- User Button -->
    <button
        class="flex items-center text-gray-700 dark:text-gray-400"
        @click.prevent="toggleDropdown()"
        type="button"
    >
       <span class="block mr-1 font-medium text-theme-sm">@if(Auth::check()) {{ Auth::user()->first_name }} @endif</span>

        <!-- Chevron Icon -->
        <svg
            class="w-5 h-5 transition-transform duration-200"
            :class="{ 'rotate-180': dropdownOpen }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Dropdown Start -->
    <div
        x-show="dropdownOpen"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-[17px] flex w-[260px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-dark z-50"
        style="display: none;"
    >
        <!-- User Info -->
        <div>
            <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">@if(Auth::check()) {{ Auth::user()->first_name }} {{ Auth::user()->last_name }} @endif</span>
            <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">@if(Auth::check()) {{ Auth::user()->email }} @endif</span>
        </div>

        <!-- Menu Items -->
        <ul class="flex flex-col gap-1 pt-4 pb-3 border-b border-gray-200 dark:border-gray-800">
            @php
                $menuItems = [
                    [
                        'text' => 'Edit profile',
                        'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 14.1526 4.3002 16.1184 5.61936 17.616C6.17279 15.3096 8.24852 13.5955 10.7246 13.5955H13.2746C15.7509 13.5955 17.8268 15.31 18.38 17.6167C19.6996 16.119 20.5 14.153 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5ZM17.0246 18.8566V18.8455C17.0246 16.7744 15.3457 15.0955 13.2746 15.0955H10.7246C8.65354 15.0955 6.97461 16.7744 6.97461 18.8455V18.856C8.38223 19.8895 10.1198 20.5 12 20.5C13.8798 20.5 15.6171 19.8898 17.0246 18.8566ZM2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM11.9991 7.25C10.8847 7.25 9.98126 8.15342 9.98126 9.26784C9.98126 10.3823 10.8847 11.2857 11.9991 11.2857C13.1135 11.2857 14.0169 10.3823 14.0169 9.26784C14.0169 8.15342 13.1135 7.25 11.9991 7.25ZM8.48126 9.26784C8.48126 7.32499 10.0563 5.75 11.9991 5.75C13.9419 5.75 15.5169 7.32499 15.5169 9.26784C15.5169 11.2107 13.9419 12.7857 11.9991 12.7857C10.0563 12.7857 8.48126 11.2107 8.48126 9.26784Z" fill="currentColor"/></svg>',
                        'path' => 'profile',
                        'action' => 'link'
                    ],
                    [
                        'text' => 'Change Password',
                        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>',
                        'path' => '#',
                        'action' => 'trigger-modal' // Ditandai khusus untuk memicu popup
                    ],
                ];
            @endphp

            @foreach ($menuItems as $item)
                <li>
                    @if($item['action'] === 'trigger-modal')
                        <!-- Button khusus pemicu modal -->
                        <button
                            type="button"
                            @click="closeDropdown(); $dispatch('open-change-password-modal')"
                            class="flex items-center w-full gap-3 px-3 py-2 font-medium text-left text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                        >
                            <span class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300">
                                {!! $item['icon'] !!}
                            </span>
                            {{ $item['text'] }}
                        </button>
                    @else
                        <!-- Link reguler biasa -->
                        <a
                            href="{{ $item['path'] }}"
                            class="flex items-center gap-3 px-3 py-2 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
                        >
                            <span class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300">
                                {!! $item['icon'] !!}
                            </span>
                            {{ $item['text'] }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ul>

        <!-- Sign Out -->
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
        </form>

        <a
            href="javascript:void(0)"
            class="flex items-center w-full gap-3 px-3 py-2 mt-3 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
            @click.prevent="closeDropdown(); document.getElementById('logout-form').submit();"
        >
            <span class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
            </span>
            Sign out
        </a>
    </div>
    <!-- Dropdown End -->
</div>

<x-ui.modal 
    x-data="{ open: {{ $errors->has('current_password') || $errors->has('password') ? 'true' : 'false' }} }"
    @open-change-password-modal.window="open = true" 
    :isOpen="$errors->has('current_password') || $errors->has('password')" 
    class="max-w-[500px]"
>
    <div class="px-2 py-1">
        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Change Password</h3>
        
        <form action="{{ route('password.update') }}" method="POST" class="flex flex-col gap-4">
            @csrf
            @method('PUT')

            <!-- Current Password -->
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Current Password</label>
                <input type="password" name="current_password" 
                    class="h-11 w-full rounded-lg border @error('current_password') border-red-500 @else border-gray-300 @enderror bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:bg-gray-900 dark:text-white/90 dark:border-gray-700 focus:outline-none focus:ring-3 focus:ring-brand-500/10" required />
                @error('current_password')
                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- New Password -->
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">New Password</label>
                <input type="password" name="password" 
                    class="h-11 w-full rounded-lg border @error('password') border-red-500 @else border-gray-300 @enderror bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:bg-gray-900 dark:text-white/90 dark:border-gray-700 focus:outline-none focus:ring-3 focus:ring-brand-500/10" required />
                @error('password')
                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Confirm New Password -->
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Confirm New Password</label>
                <input type="password" name="password_confirmation" 
                    class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:bg-gray-900 dark:text-white/90 dark:border-gray-700 focus:outline-none focus:ring-3 focus:ring-brand-500/10" required />
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3 mt-4 justify-end">
                <button @click="open = false" type="button"
                    class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] sm:w-auto">
                    Cancel
                </button>
                <button type="submit"
                    class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</x-ui.modal>