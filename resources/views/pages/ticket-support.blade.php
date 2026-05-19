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

        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Full Name
            </label>
            <input type="text"
                name="requester_name"
                value="{{ old('requester_name') }}"
                placeholder="Enter your full name..."
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
            @error('requester_name')
                <p class="text-theme-xs text-error-500 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-4">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Email
            </label>
            <input type="email"
                name="requester_email"
                id="requester_email"
                value="{{ old('requester_email') }}"
                placeholder="Enter your email..."
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
            @error('requester_email')
                <p class="text-theme-xs text-error-500 mt-1.5">{{ $message }}</p>
            @enderror
            <p id="email-domain-error" class="text-theme-xs text-error-500 mt-1.5 hidden"></p>
        </div>

        <div class="mt-4">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Phone Number
            </label>
            <input type="text"
                name="phone_number"
                value="{{ old('phone_number') }}"
                placeholder="Enter your phone number..."
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
            @error('phone_number')
                <p class="text-theme-xs text-error-500 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-4">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Partner Team
            </label>
            <select name="partner_name"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                <option value="" selected>Select Partner Team</option>
                @foreach ($partners as $partner)
                    <option value="{{ $partner->code }}">{{ $partner->name }}</option>
                @endforeach
            </select>
            @error('partner_name')
                <p class="text-theme-xs text-error-500 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-4">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Issue Description
            </label>
            <textarea name="description"
                placeholder="Enter an issue description..."
                rows="6"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">{{ old('description') }}</textarea>
            @error('description')
                <p class="text-theme-xs text-error-500 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-5">
            <x-ui.button id="ticket-support-submit" size="sm" variant="primary" type="submit" disabled>Submit Ticket</x-ui.button>
        </div>
    </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ticketForm = document.getElementById('ticket-support-form');
            const submitButton = document.getElementById('ticket-support-submit');
            const fields = [
                ticketForm.querySelector('[name="requester_name"]'),
                ticketForm.querySelector('[name="requester_email"]'),
                ticketForm.querySelector('[name="phone_number"]'),
                ticketForm.querySelector('[name="partner_name"]'),
                ticketForm.querySelector('[name="description"]'),
            ];
            const emailError = document.getElementById('email-domain-error');
            const emailRegex = /^[A-Za-z0-9._%+-]+@kreston\.co\.id$/i;

            function validateForm() {
                const allFilled = fields.every(field => field && field.value.trim() !== '');
                const emailValue = fields[1]?.value.trim() || '';
                const emailValid = emailRegex.test(emailValue);

                if (emailValue !== '' && !emailValid) {
                    emailError.textContent = 'Email harus menggunakan domain @kreston.co.id.';
                    emailError.classList.remove('hidden');
                } else {
                    emailError.textContent = '';
                    emailError.classList.add('hidden');
                }

                const enabled = allFilled && emailValid;
                submitButton.disabled = !enabled;
                submitButton.classList.toggle('opacity-50', !enabled);
                submitButton.classList.toggle('cursor-not-allowed', !enabled);
            }

            fields.forEach(field => {
                if (!field) {
                    return;
                }
                field.addEventListener('input', validateForm);
                field.addEventListener('change', validateForm);
            });

            validateForm();

            if (ticketForm && submitButton) {
                ticketForm.addEventListener('submit', function () {
                    submitButton.disabled = true;
                    submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                    submitButton.innerText = 'Sending...';
                });
            }
        });
    </script>

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
                        <th class="px-6 py-3 text-start text-theme-xs font-medium text-gray-500">Team</th>
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
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                        <tr class="border-b border-gray-100 dark:border-white/[0.05] hover:bg-gray-50 dark:hover:bg-white/[0.02] cursor-pointer transition-colors" onclick="window.location.href='{{ route('ticket-support.edit', $ticket->id) }}'">
                            <td class="px-6 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400">{{ $ticket->id_ticket }}</td>
                            <td class="px-6 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400">{{ optional($ticket->request_date)->format('Y-m-d') }}</td>
                            <td class="px-6 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400">{{ $ticket->requester_name }}</td>
                            <td class="px-6 py-3.5 text-theme-sm text-gray-700 dark:text-gray-400">{{ $ticket->partner_name }}</td>
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
