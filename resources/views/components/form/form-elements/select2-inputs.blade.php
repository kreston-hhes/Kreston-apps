<x-common.component-card title="Select Inputs 2">
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Select Input 2
        </label>
        
        <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
    <select
        id="select2-setup"
        class="hidden" {{-- Sembunyikan yang asli --}}
    >
        <option value="">Select Option</option>
        <option value="marketing">Marketing</option>
        <option value="template">Template</option>
        <option value="development">Development</option>
    </select>

    {{-- SVG Panah kamu tetap di sini --}}
    <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
        <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </span>
</div>

<script type="module">
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof $ !== 'undefined') {
            $('#select2-setup').select2({
                placeholder: "Select Option",
                width: '100%',
                // Ini penting agar dropdown ikut tema dark/light
                dropdownCssClass: "custom-select2-dropdown" 
            });

            // Update class pada select
            $('#select2-setup').on('change', function() {
                const data = $(this).val();
                console.log("Data terpilih:", data);
            });
        }
    });
</script>
</div>

{{-- multiple select --}}
</x-common.component-card>