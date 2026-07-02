@props([
    'id' => null,
    'name',
    'label' => null,
    'value' => 1,
    'checked' => false,
    'disabled' => false,
    'options' => [],
    'requiredMessage' => 'Silakan pilih minimal satu.',
])

@php
    $isMultiple = str_ends_with($name, '[]');

    $oldValue = old($name);

    if ($oldValue === null) {
        $oldValue = [];
    }

    if (!is_array($oldValue)) {
        $oldValue = [$oldValue];
    }

    $optionList = [];

    if (!empty($options)) {
        foreach ($options as $index => $option) {
            $optionValue = $option['value'] ?? '';

            $optionList[] = [
                'id' => $option['id'] ?? ($id ? "{$id}-{$index}" : str_replace(['[', ']'], '', $name) . "-{$index}"),
                'value' => $optionValue,
                'label' => $option['label'] ?? $optionValue,
                'disabled' => (bool) ($option['disabled'] ?? false),
                'checked' =>
                    old($name) !== null ? in_array($optionValue, $oldValue) : (bool) ($option['checked'] ?? false),
            ];
        }
    } else {
        $optionList[] = [
            'id' => $id ?? str_replace(['[', ']'], '', $name),
            'value' => $value,
            'label' => $label,
            'disabled' => $disabled,
            'checked' =>
                old($name) !== null ? ($isMultiple ? in_array($value, $oldValue) : old($name) == $value) : $checked,
        ];
    }
@endphp

<div class="space-y-2">

    @if ($label && count($optionList) > 1)
        <div class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}


        </div>
    @endif

    @foreach ($optionList as $option)
        <label for="{{ $option['id'] }}" @class([
            'flex items-center gap-3 cursor-pointer select-none',
            'opacity-60 cursor-not-allowed' => $option['disabled'],
        ])>

            <input id="{{ $option['id'] }}" type="checkbox" name="{{ $name }}" value="{{ $option['value'] }}"
                @checked($option['checked']) @disabled($option['disabled']) {{ $attributes->except('class') }}
                class="peer sr-only" oninvalid="this.setCustomValidity('{{ $requiredMessage }}')"
                onchange="this.setCustomValidity('')">

            <div
                class="
                    flex
                    h-5
                    w-5
                    items-center
                    justify-center
                    rounded
                    border
                    border-gray-300
                    dark:border-gray-600
                    transition-all

                    peer-checked:border-brand-500
                    peer-checked:bg-brand-500

                    peer-focus:ring-2
                    peer-focus:ring-brand-300
                ">
                <svg class="h-3.5 w-3.5 text-white scale-0 transition-transform peer-checked:scale-100"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <span @class([
                'text-sm',
                'text-gray-700 dark:text-gray-300' => !$option['disabled'],
                'text-gray-400 dark:text-gray-500' => $option['disabled'],
            ])>
                {{ $option['label'] }}
                @if ($attributes->has('required'))
                    <span class="text-red-500">*</span>
                @endif
            </span>

        </label>
    @endforeach

    @error(str_replace('[]', '', $name))
        <p class="mt-1 text-sm text-red-500">
            {{ $message }}
        </p>
    @enderror

</div>
