@props([
    'name',
    'label' => '',
    'requiredMessage' => 'Dipilih dulu dong',
    'create' => false,
    'direction' => 'asc',
    'maxItems' => 1,
    'multiple' => false,
])

@php
    $isMultiple = $multiple || str_ends_with((string) $name, '[]') || (int) $maxItems > 1;
@endphp

<div x-data x-init="new TomSelect($refs.select, {
    create: {{ $create }},
    maxItems: {{ $isMultiple ? $maxItems : 1 }},
    sortField: {
        field: 'text',
        direction: '{{ $direction }}'
    }
});">


    @if ($label)
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            {{ $label }}

            @if ($attributes->has('required'))
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <select required oninvalid="
            this.setCustomValidity('{{ $requiredMessage }}')"
        onchange="this.setCustomValidity('')" x-ref="select" name="{{ $name }}" {{ $isMultiple ? 'multiple' : '' }}
        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 border-gray-300 bg-transparent text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
        {{ $attributes->merge([
            'class' => 'w-full rounded-lg border border-gray-300',
        ]) }}>

        {{ $slot }}

    </select>

</div>
