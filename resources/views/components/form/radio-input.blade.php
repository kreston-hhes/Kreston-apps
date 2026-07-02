@props([
    'id' => null,
    'name',
    'label' => null,
    'value' => null,
    'checked' => false,
    'disabled' => false,
    'options' => [],
])

@php
    $oldValue = old($name);

    $items = [];

    if (count($options)) {
        foreach ($options as $index => $option) {
            $optionValue = $option['value'] ?? '';

            $items[] = [
                'id' => $option['id'] ?? ($id ? "{$id}-{$index}" : "{$name}-{$index}"),
                'label' => $option['label'] ?? $optionValue,
                'value' => $optionValue,
                'disabled' => (bool) ($option['disabled'] ?? false),
                'checked' => $oldValue !== null ? $oldValue == $optionValue : (bool) ($option['checked'] ?? false),
            ];
        }
    } else {
        $items[] = [
            'id' => $id ?? $name,
            'label' => $label,
            'value' => $value,
            'disabled' => $disabled,
            'checked' => $oldValue !== null ? $oldValue == $value : $checked,
        ];
    }

    $inputAttributes = $attributes->except(['class']);
@endphp

<div>

    @if ($label && count($items) > 1)
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}
            @if ($attributes->has('required'))
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="space-y-3">

        @foreach ($items as $item)
            <label for="{{ $item['id'] }}" @class([
                'flex items-center gap-3 select-none',
                'cursor-pointer' => !$item['disabled'],
                'cursor-not-allowed opacity-60' => $item['disabled'],
                $attributes->get('class'),
            ])>

                <input id="{{ $item['id'] }}" name="{{ $name }}" type="radio" value="{{ $item['value'] }}"
                    @checked($item['checked']) @disabled($item['disabled']) @required($attributes->has('required')) {{ $inputAttributes }}
                    class="peer sr-only">

                <div
                    class="
                        flex
                        h-5
                        w-5
                        items-center
                        justify-center
                        rounded-full
                        border
                        border-gray-300
                        bg-white
                        transition

                        dark:border-gray-600
                        dark:bg-gray-900

                        peer-checked:border-brand-500
                        peer-checked:bg-brand-500

                        peer-focus:ring-2
                        peer-focus:ring-brand-300
                    ">

                    <div
                        class="
                            h-2
                            w-2
                            rounded-full
                            bg-white
                            scale-0
                            transition-transform
                            peer-checked:scale-100
                        ">
                    </div>

                </div>

                <span @class([
                    'text-sm',
                    'text-gray-700 dark:text-gray-300' => !$item['disabled'],
                    'text-gray-400 dark:text-gray-600' => $item['disabled'],
                ])>
                    {{ $item['label'] }}


                </span>

            </label>
        @endforeach

    </div>

    @error($name)
        <p class="mt-2 text-sm text-red-500">
            {{ $message }}
        </p>
    @enderror

</div>
