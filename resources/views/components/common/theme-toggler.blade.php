<div x-data="{
    theme: localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
}"
    x-init="document.documentElement.classList.toggle('dark', theme === 'dark');
            $watch('theme', value => {
                document.documentElement.classList.toggle('dark', value === 'dark');
                localStorage.setItem('theme', value);
            })"
>
    <button type="button"
        @click="theme = theme === 'dark' ? 'light' : 'dark'"
        class="inline-flex items-center justify-center rounded-full border border-gray-200 bg-white p-2 text-gray-700 shadow-sm transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:hover:bg-gray-800"
        aria-label="Toggle theme"
    >
        <span x-show="theme === 'light'">🌙</span>
        <span x-show="theme === 'dark'">☀️</span>
    </button>
</div>
