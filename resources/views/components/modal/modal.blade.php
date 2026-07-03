@props(['name', 'title' => 'Modal Title'])

<div x-data="{ open: false }"
    x-on:open-modal.window="
        if($event.detail == '{{ $name }}') {
            open = true
        }
    "
    x-on:close-modal.window="
        if($event.detail == '{{ $name }}') {
            open = false
        }
    ">

    {{-- BACKDROP --}}
    <div x-show="open" x-transition class="fixed inset-0 z-99998 bg-black/50"></div>

    {{-- MODAL --}}
    <div x-show="open" x-transition class="fixed inset-0 z-99999 flex items-center justify-center p-4">

        <div @click.away="open = false"
            class="flex flex-col w-full w-full-lg max-h-[90vh] rounded-2xl bg-white shadow-xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800 transition-all">
            {{-- HEADER --}}
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                    {{ $title }}
                </h2>
                <button type="button" @click="open = false"
                    class="text-gray-400 hover:text-red-500 transition text-lg">
                    ✕
                </button>
            </div>

            {{-- BODY --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar">
                {{ $slot }}
            </div>

        </div>

    </div>

</div>
