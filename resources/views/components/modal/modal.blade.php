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
    <div x-show="open" x-transition class="fixed inset-0 z-40 bg-black/50"></div>

    {{-- MODAL --}}
    <div x-show="open" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4">

        <div @click.away="open = false" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">

            {{-- HEADER --}}
            <div class="mb-4 flex items-center justify-between">

                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                    {{ $title }}
                </h2>

                <button @click="open = false" class="text-gray-400 hover:text-red-500">
                    ✕
                </button>

            </div>

            {{-- BODY --}}
            <div>

                {{ $slot }}

            </div>

        </div>

    </div>

</div>
