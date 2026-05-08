@if (session('notification'))
    <div 
        x-data="{ show: true }" 
        x-show="show" 
        x-init="setTimeout(() => show = false, 3000)" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-10" {{-- Muncul dari bawah --}}
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        {{-- Pindahkan ke bottom-6 right-6 --}}
        class="fixed bottom-6 right-6 z-[9999] w-full max-w-sm"
    >
        <x-ui.alert 
            :variant="session('notification.variant')" 
            :title="session('notification.title')" 
            :message="session('notification.message')" 
            :showLink="false"
        />
    </div>
@endif