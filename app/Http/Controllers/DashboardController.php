<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use App\Models\Employee;
use App\Models\Partnership;
use App\Models\Tickets;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('pages.dashboard');
    }


    public function showTicketSupport(Request $request)
    {
        $partners = Partnership::all();

        $ticketsQuery = Tickets::query();

        $sortAllowed = ['id_ticket', 'request_date', 'status', 'assigned_to'];
        $sort = $request->get('sort');
        $direction = $request->get('direction') === 'asc' ? 'asc' : 'desc';

        if (in_array($sort, $sortAllowed, true)) {
            $ticketsQuery->orderBy($sort, $direction);
        } else {
            $ticketsQuery->latest('id_ticket');
        }

        $tickets = $ticketsQuery->paginate(10)->withQueryString();

        return view('pages.ticket-support', [
            'title' => 'Ticket Support',
            'partners' => $partners,
            'tickets' => $tickets,
        ]);
    }

    public function submitTicket(Request $request): RedirectResponse
    {
        // Validasi input
        $request->validate([
            'requester_name' => 'required|string|max:255',
            'requester_email' => ['required', 'email', 'max:255', 'regex:/^[A-Za-z0-9._%+-]+@kreston\.co\.id$/i'],
            'partner_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:50',
            'description' => 'required|string',
        ], [
            'requester_email.regex' => 'Email harus menggunakan domain @kreston.co.id.',
        ]);

        DB::beginTransaction();

    try {

        // Format tahun + bulan
        $prefix = Carbon::now()->format('Ym');

        // Ambil ticket terakhir bulan ini
        $lastTicket = Tickets::where('id_ticket', 'like', $prefix . '%')
            ->orderBy('id_ticket', 'desc')
            ->lockForUpdate()
            ->first();

        if ($lastTicket) {

            // Ambil 4 digit terakhir
            $lastNumber = substr($lastTicket->id_ticket, -4);

            // Increment
            $newNumber = ((int) $lastNumber) + 1;

        } else {

            // Jika belum ada ticket bulan ini
            $newNumber = 1;
        }

        // Format jadi 4 digit
        $sequence = str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        // Final ticket number
        $ticketNumber = $prefix . $sequence;

        // Simpan ticket
        $ticket = Tickets::create([
            'id_ticket' => $ticketNumber,
            'request_date' => Carbon::now()->toDateString(),
            'requester_name' => $request->requester_name,
            'requester_email' => $request->requester_email,
            'partner_name' => $request->partner_name,
            'phone_number' => $request->phone_number,
            'issue_description' => $request->description,
            'status' => 'open',
        ]);

        // Cek apakah gagal save
        if (!$ticket) {

            DB::rollBack();

            AlertService::notify(
                'error',
                'Failed',
                'Failed to submit support ticket.'
            );

            return back();
        }

        DB::commit();

        AlertService::notify(
            'success',
            'Ticket Submitted',
            'Your support ticket has been submitted successfully.'
        );

        return back();

    } catch (\Exception $e) {

        DB::rollBack();

        // Logging error
        logger()->error('Submit Ticket Error: ' . $e->getMessage());

        AlertService::notify(
            'error',
            'System Error',
            'An error occurred while submitting the ticket.'
        );

        return back()->withInput();
    }

    }

    public function editTicketSupport($id)
    {
        $ticket = Tickets::findOrFail($id);
        $partners = Partnership::all();

        return view('pages.ticket-support-edit', [
            'title' => 'Edit Ticket Support',
            'ticket' => $ticket,
            'partners' => $partners,
        ]);
    }

    public function updateTicketSupport(Request $request, $id): RedirectResponse
    {
        $ticket = Tickets::findOrFail($id);

        $request->validate([
            'requester_name' => 'required|string|max:255',
            'requester_email' => ['required', 'email', 'max:255', 'regex:/^[A-Za-z0-9._%+-]+@kreston\.co\.id$/i'],
            'partner_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:50',
            'description' => 'required|string',
        ], [
            'requester_email.regex' => 'Email harus menggunakan domain @kreston.co.id.',
        ]);

        DB::beginTransaction();

        try {
            $ticket->requester_name = $request->requester_name;
            $ticket->requester_email = $request->requester_email;
            $ticket->partner_name = $request->partner_name;
            $ticket->phone_number = $request->phone_number;
            $ticket->issue_description = $request->description;
            $ticket->save();

            DB::commit();

            AlertService::notify('success', 'Updated', 'Ticket details have been updated.');
            return redirect()->route('ticket-support');
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Update Ticket Error: ' . $e->getMessage());

            AlertService::notify('error', 'System Error', 'Unable to update the ticket.');
            return back()->withInput();
        }
    }

    public function startTicket(Request $request, $id): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $ticket = Tickets::findOrFail($id);

            if ($ticket->status !== 'open') {
                AlertService::notify('error', 'Invalid Action', 'Ticket is not open.');
                return back();
            }

            $user = auth()->user();
            $assignedTo = ($user->first_name ?? '') . ' ' . ($user->last_name ?? '');

            $ticket->status = 'in_progress';
            $ticket->assigned_to = trim($assignedTo);
            $ticket->save();

            DB::commit();

            AlertService::notify('success', 'Started', 'Ticket moved to in progress.');
            return back();

        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Start Ticket Error: ' . $e->getMessage());
            AlertService::notify('error', 'System Error', 'Unable to start ticket.');
            return back();
        }
    }

    public function closeTicket(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'resolution' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $ticket = Tickets::findOrFail($id);

            if ($ticket->status !== 'in_progress') {
                AlertService::notify('error', 'Invalid Action', 'Ticket is not in progress.');
                return back();
            }

            $ticket->status = 'closed';
            $ticket->resolution = $request->resolution;
            $ticket->updated_at = Carbon::now();
            $ticket->save();

            DB::commit();

            AlertService::notify('success', 'Closed', 'Ticket has been closed.');
            return redirect()->route('ticket-support');

        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Close Ticket Error: ' . $e->getMessage());
            AlertService::notify('error', 'System Error', 'Unable to close ticket.');
            return back()->withInput();
        }
    }
    }
