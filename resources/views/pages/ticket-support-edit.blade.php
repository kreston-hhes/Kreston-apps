@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Ticket Support Detail" />
    <div class="min-h-screen rounded-2xl border border-gray-200 bg-white px-5 py-7 dark:border-gray-800 dark:bg-white/[0.03] xl:px-10 xl:py-12">
        <div class="mx-auto w-full max-w-[630px]">
            <p class="mb-4 font-semibold text-gray-800 text-theme-l dark:text-white/90 sm:text-l">
                Ticket Support Details
            </p>

            @if ($ticket->status == 'open')
                <form action="{{ route('ticket-start', $ticket->id) }}" method="POST" class="inline">
                    @csrf

                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Ticket Number</label>
                        <input type="text" value="{{ $ticket->id_ticket }}" disabled
                            class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Request Date</label>
                        <input type="text" value="{{ optional($ticket->request_date)->format('d-m-Y') }}" disabled
                            class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Requester Name</label>
                        <input type="text" value="{{ $ticket->requester_name }}" disabled
                            class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Partner Team</label>
                        <input type="text" value="{{ $ticket->partner_name }}" disabled
                            class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div class="mb-6">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Issue Description</label>
                        <textarea rows="6" disabled
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">{{ $ticket->issue_description }}</textarea>
                    </div>

                    <div class="flex items-center gap-3">
                        <x-ui.button id="ticket-support-start" size="sm" variant="primary" type="submit">Start Ticket</x-ui.button>
                        <a href="{{ route('ticket-support') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300">Back to List</a>
                    </div>
                </form>
            @elseif ($ticket->status == 'in_progress')
                <form action="{{ route('ticket-close', $ticket->id) }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Ticket Number</label>
                        <input type="text" value="{{ $ticket->id_ticket }}" disabled
                            class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Request Date</label>
                        <input type="text" value="{{ optional($ticket->request_date)->format('d-m-Y') }}" disabled
                            class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Requester Name</label>
                        <input type="text" value="{{ $ticket->requester_name }}" disabled
                            class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div class="mb-4">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Partner Team</label>
                        <input type="text" value="{{ $ticket->partner_name }}" disabled
                            class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div class="mb-6">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Issue Description</label>
                        <textarea rows="6" disabled
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">{{ $ticket->issue_description }}</textarea>
                    </div>

                    <div class="mb-6">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Resolution</label>
                        <textarea name="resolution" rows="6" placeholder="Enter resolution for this ticket..."
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">{{ old('resolution', $ticket->resolution) }}</textarea>
                        @error('resolution')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <x-ui.button id="ticket-support-close" size="sm" variant="primary" type="submit">Close Ticket</x-ui.button>
                        <a href="{{ route('ticket-support') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300">Back to List</a>
                    </div>
                </form>
            @else
                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Ticket Number</label>
                    <input type="text" value="{{ $ticket->id_ticket }}" disabled
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>

                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Request Date</label>
                    <input type="text" value="{{ optional($ticket->request_date)->format('d-m-Y') }}" disabled
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>

                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Requester Name</label>
                    <input type="text" value="{{ $ticket->requester_name }}" disabled
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>

                <div class="mb-4">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Partner Team</label>
                    <input type="text" value="{{ $ticket->partner_name }}" disabled
                        class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>

                <div class="mb-6">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Issue Description</label>
                    <textarea rows="6" disabled
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">{{ $ticket->issue_description }}</textarea>
                </div>

                <div class="mb-6">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Resolution</label>
                    <textarea rows="6" disabled
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">{{ $ticket->resolution }}</textarea>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('ticket-support') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300">Back to Ticket List</a>
                </div>
            @endif


        </div>
    </div>
@endsection
