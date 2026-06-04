@props(['name'])

<div x-data x-init="new TomSelect($refs.select, {
    create: false,
    closeAfterSelect: true,
    sortField: {
        field: 'text',
        direction: 'asc'
    }
});">

    <select x-ref="select" name="{{ $name }}" {{ $attributes }}>

        {{ $slot }}

    </select>

</div>
