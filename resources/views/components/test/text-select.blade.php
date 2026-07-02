@props(['name', 'label' => '', 'requiredMessage' => 'Dipilih dulu dong'])

<div x-data x-init="new TomSelect($refs.select, {
    create: true,
    sortField: {
        field: 'text',
        direction: 'asc'
    }
});">


    @if ($label)
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            {{ $label }}
        </label>
    @endif

    <select required oninvalid="
            this.setCustomValidity('{{ $requiredMessage }}')"
        onchange="this.setCustomValidity('')" x-ref="select" name="{{ $name }}"
        {{ $attributes->merge([
            'class' => 'w-full rounded-lg border border-gray-300',
        ]) }}>

        {{ $slot }}

    </select>

</div>
