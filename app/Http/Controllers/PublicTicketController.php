<?php

namespace App\Http\Controllers;
use App\Models\Tickets;
use App\Models\Partnership;
use Illuminate\Http\Request;
use App\Services\AlertService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketSubmittedMail;
use App\Models\Employee;


class PublicTicketController extends Controller
{

public function sendBlankMail()
{

return view('emails.blank-mail',[
'title' => 'Title Mail',
'logoPath' => asset('images/logo/logo.svg'),
]);
}

    public function index()
    {
        $partners = Partnership::all();

        return view('pages.public-ticket', [
            'title' => 'Public Ticket',
            'partners' => $partners,
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
        
              // Kirim email dengan aman setelah commit database benar-benar selesai
    DB::afterCommit(function () use ($ticket) {

    $itEmails = Employee::where('division', 'IT')
        ->where('status', 'active')
        ->whereNotNull('email')
        ->pluck('email')
        ->filter()
        ->unique()
        ->values()
        ->toArray();

    Mail::to($ticket->requester_email)
        ->bcc($itEmails)
        ->send(new TicketSubmittedMail($ticket));

});
        AlertService::notify(
            'success',
            'Ticket Submitted',
            'Your support ticket ' . $ticket->id_ticket . ' has been submitted successfully.'
        );

        return back()->with('success', 'Your support ticket ' . $ticket->id_ticket);

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

    public function checkStatus($ticketNumber)
{
    $ticket = Tickets::where('id_ticket', $ticketNumber)->first();

    if (!$ticket) {
        return response()->json([
            'success' => false,
            'message' => 'Ticket not found.'
        ]);
    }

    return response()->json([
        'success' => true,
        'ticket' => [
            'ticket_number' => $ticket->id_ticket,
            'status' => $ticket->status,
            'requester_name' => $ticket->requester_name,
            'partner_team' => $ticket->partner_name,
            'description' => $ticket->issue_description,
            'solution' => $ticket->resolution ?? 'No solution provided yet.',
            'support_by' => $ticket->assigned_to ?? 'Not assigned yet.',
            'created_at' => $ticket->request_date->format('Y-m-d')  ,
        ]
    ]);
}
}
