@props([
    'id' => null,
    'name',
    'label' => null,
    'value' => null,
    'placeholder' => '',
    'rows' => 4,
    'disabled' => false,
    'readonly' => false,
    'resize' => 'vertical', // none | vertical | horizontal | both
])

@php
    $textareaId = $id ?? $name;

    $resizeClass = match ($resize) {
        'none' => 'resize-none',
        'horizontal' => 'resize-x',
        'both' => 'resize',
        default => 'resize-y',
    };
@endphp

<div>

    @if ($label)
        <label for="{{ $textareaId }}" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}

            @if ($attributes->has('required'))
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <textarea id="{{ $textareaId }}" name="{{ $name }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}"
        @disabled($disabled) @readonly($readonly)
        {{ $attributes->except('class')->merge([
            'class' => "
                                block
                                w-full
                                rounded-lg
                                border
                                border-gray-300
                                bg-white
                                px-4
                                py-2.5
                                text-sm
                                text-gray-900
                                placeholder:text-gray-400
                                transition
        
                                focus:border-brand-500
                                focus:ring-2
                                focus:ring-brand-300
                                focus:outline-none
        
                                dark:border-gray-700
                                dark:bg-gray-900
                                dark:text-white
                                dark:placeholder:text-gray-500
        
                                disabled:cursor-not-allowed
                                disabled:bg-gray-100
                                dark:disabled:bg-gray-800
        
                                {$resizeClass}
                            ",
        ]) }}>{{ old($name, $value) }}</textarea>

    @error($name)
        <p class="mt-2 text-sm text-red-500">
            {{ $message }}
        </p>
    @enderror

</div>
