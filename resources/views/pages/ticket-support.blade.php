@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Ticket Support" />
    <div class="min-h-screen rounded-2xl border border-gray-200 bg-white px-5 py-7 dark:border-gray-800 dark:bg-white/[0.03] xl:px-10 xl:py-12">
        <div class="mx-auto w-full max-w-[630px] text-center">
            <p class="mb-4 font-semibold text-gray-800 text-theme-l dark:text-white/90 sm:text-l">
                Create your support ticket for IT issues and we will get back to you as soon as possible
            </p>

            <hr class="my-6 border-gray-200 dark:border-gray-800" />
        </div>

    <div>
    <form id="ticket-support-form" action="{{ route('ticket-support.submit') }}" method="POST">
    @csrf

   {{-- multiple select --}}
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Employee Name
        </label>
        
        <div x-data="{ isOptionSelected: false }" class="relative z-20 bg-transparent">
    <select name="employee_id"
        id="select2-setup"
        class="hidden" {{-- Sembunyikan yang asli --}}
    >
        <option value="">Select Option</option>
        @foreach ($employees as $employee)
            <option value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}</option>  
        @endforeach
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
                dropdownCssClass: "custom-select2-dropdown",
                // Tambahkan class pada container agar mudah ditargetkan oleh CSS
                containerCssClass: "custom-select2-container"
            });

            // Update class pada select
            $('#select2-setup').on('change', function() {
                const data = $(this).val();
                console.log("Data terpilih:", data);
            });

            const ticketForm = document.getElementById('ticket-support-form');
            const submitButton = document.getElementById('ticket-support-submit');

            if (ticketForm && submitButton) {
                ticketForm.addEventListener('submit', function () {
                    submitButton.disabled = true;
                    submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                    submitButton.innerText = 'Submitting...';
                });
            }
        }
    });
</script>
</div>

{{-- multiple select --}}


    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Issue Description
        </label>
        <textarea  name="description"placeholder="Enter an issue description..." type="text" rows="6"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"></textarea>
    <x-ui.button id="ticket-support-submit" size="sm" variant="primary" type="submit">Submit Ticket</x-ui.button>
        </form>
        </div>

         <div class="pt-4 max-w-full overflow-x-auto">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Ticket History
            </label>
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-start">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'id_ticket', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 text-theme-xs font-medium text-gray-500 hover:text-blue-600">
                                ID Ticket
                                <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" stroke-width="2"/></svg>
                            </a>
                        </th>
                        <th class="px-6 py-3 text-start">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'request_date', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 text-theme-xs font-medium text-gray-500 hover:text-blue-600">
                                Requested at
                                <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" stroke-width="2"/></svg>
                            </a>
                        </th>
                        <th class="px-6 py-3 text-start text-theme-xs font-medium text-gray-500">Requester</th>
                        <th class="px-6 py-3 text-start text-theme-xs font-medium text-gray-500">Issue Description</th>
                        <th class="px-6 py-3 text-start">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 text-theme-xs font-medium text-gray-500 hover:text-blue-600">
                                Status
                                <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" stroke-width="2"/></svg>
                            </a>
                        </th>
                        <th class="px-6 py-3 text-start">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'assigned_to', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 text-theme-xs font-medium text-gray-500 hover:text-blue-600">
                                Supported by
                                <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" stroke-width="2"/></svg>
                            </a>
                        </th>
                        <th class="px-6 py-3 text-start text-theme-xs font-medium text-gray-500">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr class="border-b border-gray-100 dark:border-white/[0.05] hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="px-6 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400">{{ $ticket->id_ticket }}</td>
                            <td class="px-6 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400">{{ optional($ticket->request_date)->format('Y-m-d') }}</td>
                            <td class="px-6 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400">{{ $ticket->employee?->first_name }} {{ $ticket->employee?->last_name }}</td>
                            <td class="px-6 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400">{{ $ticket->issue_description }}</td>
                            <td class="px-6 py-3.5">
                                @php
                                    $statusLabel = ucfirst(str_replace('_', ' ', $ticket->status));
                                    $statusClasses = 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ';

                                    if ($ticket->status === 'open') {
                                        $statusClasses .= 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-200';
                                    } elseif ($ticket->status === 'in_progress') {
                                        $statusClasses .= 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-200';
                                    } elseif ($ticket->status === 'closed') {
                                        $statusClasses .= 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-200';
                                    } else {
                                        $statusClasses .= 'bg-gray-100 text-gray-700 dark:bg-gray-900/20 dark:text-gray-200';
                                    }
                                @endphp
                                <span class="{{ $statusClasses }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400">{{ $ticket->assigned_to ?? '-' }}</td>
                            <td class="px-6 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400">
                                @if($ticket->status === 'open')
                                    <form action="{{ route('ticket-start', $ticket->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-1 text-xs font-medium text-white hover:bg-blue-700">Start</button>
                                    </form>
                                @elseif($ticket->status === 'in_progress')
                                    <form action="{{ route('ticket-close', $ticket->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center rounded-md bg-gray-700 px-3 py-1 text-xs font-medium text-white hover:bg-gray-800">Close</button>
                                    </form>
                                @else
                                    <span class="text-sm text-gray-500">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                No tickets found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">
                {{ $tickets->links() }}
            </div>
     


    </div>
@endsection
