@extends('layouts.fullscreen-layout')

@section('content')
    <div class="relative z-1 bg-white p-6 sm:p-0 dark:bg-gray-900">
        <div class="relative flex h-screen w-full flex-col lg:flex-row dark:bg-gray-900">

            <!-- Form -->
            <div class="flex w-full flex-1 flex-col overflow-y-auto lg:w-1/2">

                <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center px-6 py-10">

                    <h2 class="mb-10 text-center text-2xl font-semibold text-gray-800 dark:text-white/90">
                        Need Help? Submit a Ticket Support
                    </h2>
                    @if (session('success'))
                        <div class="mb-4 rounded-lg bg-emerald-100 px-4 py-3 text-sm text-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-200"
                            role="alert">
                            {{ session('success') }}
                        </div>

                        <!-- Success Popup -->
                        <div id="success-popup"
                            class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4">
                            <div class="w-full max-w-md rounded-2xl bg-white p-6 text-center shadow-xl dark:bg-gray-900">

                                <div
                                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-500/20">
                                    <svg class="h-8 w-8 text-emerald-600 dark:text-emerald-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>

                                <h3 class="mt-4 text-xl font-semibold text-gray-800 dark:text-white">
                                    Success
                                </h3>

                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    {{ session('success') }}
                                </p>

                                <button id="close-success-popup"
                                    class="mt-6 w-full rounded-lg bg-emerald-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-emerald-600">
                                    OK
                                </button>

                            </div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {

                                const popup = document.getElementById('success-popup');
                                const closeButton = document.getElementById('close-success-popup');

                                if (closeButton) {

                                    closeButton.addEventListener('click', function() {

                                        popup.classList.add('hidden');

                                    });
                                }

                                // auto close 5 detik
                                setTimeout(() => {

                                    if (popup) {

                                        popup.classList.add('hidden');

                                    }

                                }, 10000);

                            });
                        </script>
                    @endif

                    <form id="ticket-support-form" action="{{ route('public-ticket-support.submit') }}" method="POST">
                        @csrf

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Full Name
                            </label>
                            <input type="text" name="requester_name" value="{{ old('requester_name') }}"
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
                            <input type="email" name="requester_email" id="requester_email"
                                value="{{ old('requester_email') }}" placeholder="Enter your email..."
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
                            <input type="text" name="phone_number" value="{{ old('phone_number') }}"
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
                                    <option value="{{ $partner->code }}">



                                        {{ $partner->name }} - [{{ $partner->code }}]</option>
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
                            <textarea name="description" placeholder="Enter an issue description..." rows="6"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-theme-xs text-error-500 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-5">
                            <button id="ticket-support-submit" type="submit" disabled
                                class="w-full rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white opacity-50 cursor-not-allowed transition hover:bg-brand-600">
                                Submit Ticket
                            </button>
                        </div>
                    </form>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {

                            const ticketForm = document.getElementById('ticket-support-form');
                            const submitButton = document.getElementById('ticket-support-submit');
                            const emailError = document.getElementById('email-domain-error');

                            if (!ticketForm || !submitButton) {
                                return;
                            }

                            const requesterName = ticketForm.querySelector('[name="requester_name"]');
                            const requesterEmail = ticketForm.querySelector('[name="requester_email"]');
                            const phoneNumber = ticketForm.querySelector('[name="phone_number"]');
                            const partnerName = ticketForm.querySelector('[name="partner_name"]');
                            const description = ticketForm.querySelector('[name="description"]');

                            const fields = [
                                requesterName,
                                requesterEmail,
                                phoneNumber,
                                partnerName,
                                description
                            ];

                            const emailRegex = /^[A-Za-z0-9._%+-]+@kreston\.co\.id$/i;

                            function validateForm() {

                                const allFilled = fields.every(field =>
                                    field && field.value.trim() !== ''
                                );

                                const emailValue = requesterEmail.value.trim();

                                const emailValid = emailRegex.test(emailValue);

                                if (emailValue !== '' && !emailValid) {

                                    emailError.textContent =
                                        'Email harus menggunakan domain @kreston.co.id.';

                                    emailError.classList.remove('hidden');

                                } else {

                                    emailError.textContent = '';

                                    emailError.classList.add('hidden');
                                }

                                const enabled = allFilled && emailValid;

                                submitButton.disabled = !enabled;

                                if (enabled) {

                                    submitButton.classList.remove(
                                        'opacity-50',
                                        'cursor-not-allowed'
                                    );

                                } else {

                                    submitButton.classList.add(
                                        'opacity-50',
                                        'cursor-not-allowed'
                                    );
                                }
                            }

                            fields.forEach(field => {

                                if (!field) return;

                                field.addEventListener('input', validateForm);

                                field.addEventListener('change', validateForm);
                            });

                            validateForm();

                            ticketForm.addEventListener('submit', function() {

                                submitButton.disabled = true;

                                submitButton.innerText = 'Sending...';

                                submitButton.classList.add(
                                    'opacity-50',
                                    'cursor-not-allowed'
                                );
                            });

                        });
                    </script>
                </div>
            </div>

            <div class="bg-brand-950 relative flex w-full items-center justify-center lg:grid lg:w-1/2 dark:bg-white/5">
                <div class="z-1 flex items-center justify-center">
                    <!-- ===== Common Grid Shape Start ===== -->
                    <x-common.common-grid-shape />
                    <div class="flex max-w-xs flex-col items-center">
                        <a href="{{ route('public-ticket.form') }}" class="mb-4 block">
                            <img src="./images/logo/auth-logo.svg" alt="Logo" />
                        </a>
                        <p class="text-center text-gray-400 dark:text-white/60">
                            Ticket Support
                        </p>
                        <input type="text" id="ticket-number-input" placeholder="Enter Ticket Number to check status..."
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-white/90 placeholder:text-white/30 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" />
                        <button id="check-ticket-status"
                            class="mt-4 w-full rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">
                            Check Status
                        </button>



                        <div id="ticket-modal"
                            class="fixed inset-0 z-99999 hidden items-center justify-center bg-black/50 p-4">
                            <div class="w-full max-w-lg rounded-2xl bg-white p-6 dark:bg-gray-900">

                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                                        Ticket Detail
                                    </h3>

                                    <button id="close-ticket-modal"
                                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400">
                                        ✕
                                    </button>
                                </div>

                                <div class="mt-6 space-y-4">

                                    <div>
                                        <p class="text-sm text-gray-500">Ticket Number</p>
                                        <p id="modal-ticket-number" class="font-medium text-gray-800 dark:text-white"></p>
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-500">Status</p>
                                        <p id="modal-ticket-status"
                                            class="inline-flex w-fit rounded-full px-3 py-1 text-sm font-medium"></p>
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-500">Requester</p>
                                        <p id="modal-ticket-requester" class="font-medium text-gray-800 dark:text-white">
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-500">Partner Team</p>
                                        <p id="modal-ticket-partner" class="font-medium text-gray-800 dark:text-white">
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-500">Description</p>
                                        <p id="modal-ticket-description"
                                            class="font-medium text-gray-800 dark:text-white"></p>
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-500">Solution</p>
                                        <p id="modal-ticket-solution" class="font-medium text-gray-800 dark:text-white">
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-500">Support By</p>
                                        <p id="modal-ticket-support" class="font-medium text-gray-800 dark:text-white">
                                        </p>
                                    </div>

                                    <div>
                                        <p class="text-sm text-gray-500">Created At</p>
                                        <p id="modal-ticket-created" class="font-medium text-gray-800 dark:text-white">
                                        </p>
                                    </div>

                                </div>
                            </div>
                        </div>


                        <script>
                            document.addEventListener('DOMContentLoaded', function() {

                                const ticketInput = document.getElementById('ticket-number-input');
                                const checkButton = document.getElementById('check-ticket-status');
                                const modal = document.getElementById('ticket-modal');
                                const closeModal = document.getElementById('close-ticket-modal');

                                async function checkTicketStatus() {

                                    const ticketNumber = ticketInput.value.trim();

                                    if (!ticketNumber) {
                                        alert('Please enter ticket number.');
                                        return;
                                    }

                                    try {

                                        checkButton.disabled = true;
                                        checkButton.innerText = 'Checking...';

                                        const response = await fetch(`/${ticketNumber}`);

                                        const data = await response.json();

                                        if (!data.success) {

                                            alert(data.message);

                                            return;
                                        }

                                        document.getElementById('modal-ticket-number').innerText =
                                            data.ticket.ticket_number;

                                        const statusElement =
                                            document.getElementById('modal-ticket-status');

                                        statusElement.innerText = data.ticket.status;

                                        statusElement.className =
                                            'inline-flex w-fit rounded-full px-3 py-1 text-sm font-medium';

                                        switch (data.ticket.status) {

                                            case 'open':

                                                statusElement.classList.add(
                                                    'bg-red-100',
                                                    'text-red-700',
                                                    'dark:bg-red-500/15',
                                                    'dark:text-red-400'
                                                );

                                                break;

                                            case 'in_progress':

                                                statusElement.classList.add(
                                                    'bg-yellow-100',
                                                    'text-yellow-700',
                                                    'dark:bg-yellow-500/15',
                                                    'dark:text-yellow-400'
                                                );

                                                break;

                                            case 'closed':

                                                statusElement.classList.add(
                                                    'bg-green-100',
                                                    'text-green-700',
                                                    'dark:bg-green-500/15',
                                                    'dark:text-green-400'
                                                );

                                                break;

                                            default:

                                                statusElement.classList.add(
                                                    'bg-gray-100',
                                                    'text-gray-700',
                                                    'dark:bg-gray-500/15',
                                                    'dark:text-gray-400'
                                                );
                                        }

                                        document.getElementById('modal-ticket-requester').innerText =
                                            data.ticket.requester_name;

                                        document.getElementById('modal-ticket-partner').innerText =
                                            data.ticket.partner_team;

                                        document.getElementById('modal-ticket-description').innerText =
                                            data.ticket.description;

                                        document.getElementById('modal-ticket-solution').innerText =
                                            data.ticket.solution;

                                        document.getElementById('modal-ticket-support').innerText =
                                            data.ticket.support_by;

                                        document.getElementById('modal-ticket-created').innerText =
                                            data.ticket.created_at;

                                        modal.classList.remove('hidden');
                                        modal.classList.add('flex');

                                    } catch (error) {

                                        console.error(error);

                                        alert('Failed to fetch ticket status.');

                                    } finally {

                                        checkButton.disabled = false;
                                        checkButton.innerText = 'Check Status';
                                    }
                                }

                                // ENTER di input
                                ticketInput.addEventListener('keypress', function(e) {

                                    if (e.key === 'Enter') {

                                        e.preventDefault();

                                        checkTicketStatus();
                                    }
                                });

                                // CLICK button
                                checkButton.addEventListener('click', function() {

                                    checkTicketStatus();
                                });

                                // CLOSE MODAL
                                closeModal.addEventListener('click', function() {

                                    modal.classList.add('hidden');
                                    modal.classList.remove('flex');
                                });

                            });
                        </script>
                    </div>
                </div>
            </div>
            <!-- Toggler -->
            <div class="fixed right-6 bottom-6 z-50">
                <button
                    class="bg-brand-500 hover:bg-brand-600 inline-flex size-14 items-center justify-center rounded-full text-white transition-colors"
                    @click.prevent="$store.theme.toggle()">
                    <svg class="hidden fill-current dark:block" width="20" height="20" viewBox="0 0 20 20"
                        fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M9.99998 1.5415C10.4142 1.5415 10.75 1.87729 10.75 2.2915V3.5415C10.75 3.95572 10.4142 4.2915 9.99998 4.2915C9.58577 4.2915 9.24998 3.95572 9.24998 3.5415V2.2915C9.24998 1.87729 9.58577 1.5415 9.99998 1.5415ZM10.0009 6.79327C8.22978 6.79327 6.79402 8.22904 6.79402 10.0001C6.79402 11.7712 8.22978 13.207 10.0009 13.207C11.772 13.207 13.2078 11.7712 13.2078 10.0001C13.2078 8.22904 11.772 6.79327 10.0009 6.79327ZM5.29402 10.0001C5.29402 7.40061 7.40135 5.29327 10.0009 5.29327C12.6004 5.29327 14.7078 7.40061 14.7078 10.0001C14.7078 12.5997 12.6004 14.707 10.0009 14.707C7.40135 14.707 5.29402 12.5997 5.29402 10.0001ZM15.9813 5.08035C16.2742 4.78746 16.2742 4.31258 15.9813 4.01969C15.6884 3.7268 15.2135 3.7268 14.9207 4.01969L14.0368 4.90357C13.7439 5.19647 13.7439 5.67134 14.0368 5.96423C14.3297 6.25713 14.8045 6.25713 15.0974 5.96423L15.9813 5.08035ZM18.4577 10.0001C18.4577 10.4143 18.1219 10.7501 17.7077 10.7501H16.4577C16.0435 10.7501 15.7077 10.4143 15.7077 10.0001C15.7077 9.58592 16.0435 9.25013 16.4577 9.25013H17.7077C18.1219 9.25013 18.4577 9.58592 18.4577 10.0001ZM14.9207 15.9806C15.2135 16.2735 15.6884 16.2735 15.9813 15.9806C16.2742 15.6877 16.2742 15.2128 15.9813 14.9199L15.0974 14.036C14.8045 13.7431 14.3297 13.7431 14.0368 14.036C13.7439 14.3289 13.7439 14.8038 14.0368 15.0967L14.9207 15.9806ZM9.99998 15.7088C10.4142 15.7088 10.75 16.0445 10.75 16.4588V17.7088C10.75 18.123 10.4142 18.4588 9.99998 18.4588C9.58577 18.4588 9.24998 18.123 9.24998 17.7088V16.4588C9.24998 16.0445 9.58577 15.7088 9.99998 15.7088ZM5.96356 15.0972C6.25646 14.8043 6.25646 14.3295 5.96356 14.0366C5.67067 13.7437 5.1958 13.7437 4.9029 14.0366L4.01902 14.9204C3.72613 15.2133 3.72613 15.6882 4.01902 15.9811C4.31191 16.274 4.78679 16.274 5.07968 15.9811L5.96356 15.0972ZM4.29224 10.0001C4.29224 10.4143 3.95645 10.7501 3.54224 10.7501H2.29224C1.87802 10.7501 1.54224 10.4143 1.54224 10.0001C1.54224 9.58592 1.87802 9.25013 2.29224 9.25013H3.54224C3.95645 9.25013 4.29224 9.58592 4.29224 10.0001ZM4.9029 5.9637C5.1958 6.25659 5.67067 6.25659 5.96356 5.9637C6.25646 5.6708 6.25646 5.19593 5.96356 4.90303L5.07968 4.01915C4.78679 3.72626 4.31191 3.72626 4.01902 4.01915C3.72613 4.31204 3.72613 4.78692 4.01902 5.07981L4.9029 5.9637Z"
                            fill="" />
                    </svg>
                    <svg class="fill-current dark:hidden" width="20" height="20" viewBox="0 0 20 20"
                        fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M17.4547 11.97L18.1799 12.1611C18.265 11.8383 18.1265 11.4982 17.8401 11.3266C17.5538 11.1551 17.1885 11.1934 16.944 11.4207L17.4547 11.97ZM8.0306 2.5459L8.57989 3.05657C8.80718 2.81209 8.84554 2.44682 8.67398 2.16046C8.50243 1.8741 8.16227 1.73559 7.83948 1.82066L8.0306 2.5459ZM12.9154 13.0035C9.64678 13.0035 6.99707 10.3538 6.99707 7.08524H5.49707C5.49707 11.1823 8.81835 14.5035 12.9154 14.5035V13.0035ZM16.944 11.4207C15.8869 12.4035 14.4721 13.0035 12.9154 13.0035V14.5035C14.8657 14.5035 16.6418 13.7499 17.9654 12.5193L16.944 11.4207ZM16.7295 11.7789C15.9437 14.7607 13.2277 16.9586 10.0003 16.9586V18.4586C13.9257 18.4586 17.2249 15.7853 18.1799 12.1611L16.7295 11.7789ZM10.0003 16.9586C6.15734 16.9586 3.04199 13.8433 3.04199 10.0003H1.54199C1.54199 14.6717 5.32892 18.4586 10.0003 18.4586V16.9586ZM3.04199 10.0003C3.04199 6.77289 5.23988 4.05695 8.22173 3.27114L7.83948 1.82066C4.21532 2.77574 1.54199 6.07486 1.54199 10.0003H3.04199ZM6.99707 7.08524C6.99707 5.52854 7.5971 4.11366 8.57989 3.05657L7.48132 2.03522C6.25073 3.35885 5.49707 5.13487 5.49707 7.08524H6.99707Z"
                            fill="" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
@endsection
