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
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Hash;



class DashboardController extends Controller
{
public function index()
    {
        return view('pages.dashboard');
    }


public function updatePassword(Request $request): RedirectResponse
{
       
        $user = auth()->user();

        try {

        // Tulis pesan kustom di sini
        $customMessages = [
            'current_password.required' => 'Current password is required.',
            'current_password.current_password' => 'Current password is incorrect.',
            'password.required' => 'New password is required.',
            'password.min' => 'New password must be at least :min characters.',
            'password.confirmed' => 'The new password confirmation does not match.',
        ];

            // 1. Validasi Input Password
            $validator = Validator::make($request->all(), [
                'current_password' => ['required', 'current_password'], // Memastikan password lama cocok dengan DB
                'password'         => ['required', 'string', 'min:8', 'confirmed'], // Wajib sama dengan password_confirmation
            ], $customMessages);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // 2. Ambil data password baru, enkripsi dengan Hash, lalu simpan
            $user->update([
                'password' => Hash::make($request->input('password')),
            ]);

            // 3. Trigger Alert Sukses Ganti Password
            AlertService::notify(
                'success',
                'Password Changed',
                'Your password has been changed successfully.'
            );

            return redirect()->back();

        } catch (ValidationException $e) {
            AlertService::notify(
                'error',
                'Password Update Failed',
                'Please check your password inputs.'
            );
            return redirect()->back()->withErrors($e->validator);

        } catch (Exception $e) {
            AlertService::notify(
                'error',
                'System Error',
                'Failed to update password: ' . $e->getMessage()
            );
            return redirect()->back();
        }
    }



public function showProfile()
    {
        $user = auth()->user();
        $employee = Employee::with('partnership', 'manager')->where('email', $user->email)->first();

        return view('pages.profile', [
            'title' => 'Profile',
            'employee' => $employee,
        ]);
    }

 public function updateProfile(Request $request): RedirectResponse
    {
        $user = auth()->user();
       

        try{

        
        $employee = Employee::where('email', $user->email)->firstOrFail();
   // 1. Buat validator manual agar error-nya bisa kita tangkap di blok catch
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'nullable|string|max:255',
            'phone'      => 'nullable|string|max:50',
            'address'    => 'nullable|string|max:500',
            'gender'     => 'in:male,female',
            'birth_date' => 'nullable|date',
        ]);

// Jika validasi gagal, lempar ValidationException agar ditangkap oleh catch pertama 
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
// 2. Ambil data yang sudah tervalidasi dan lakukan update 
        $validatedData = $validator->validated();
        $employee->update($validatedData);

        $gender = $request->input('gender');// 3. Trigger Alert Sukses
        AlertService::notify(
            'success',
            'Profile Updated',
            'Your profile has been updated successfully.'
        );

        return redirect()->route('profile.show');
    } catch (ValidationException $e) {
        // Tangkap jika error-nya disebabkan karena input form tidak valid
        AlertService::notify(
            'error',
            'Update Failed',
            'Please check the form for errors.'
        );

        // redirect()->back() wajib menyertakan withErrors dan withInput 
        // supaya pesan error di bawah input form dan data lama tidak hilang
        return redirect()->back()->withErrors($e->validator)->withInput();

    } catch (Exception $e) {
        // Tangkap jika terjadi error tidak terduga lainnya (misal: koneksi database putus, dll)
        AlertService::notify(
            'error',
            'System Error',
            'Something went wrong: ' . $e->getMessage()
        );

        return redirect()->back()->withInput();
    }
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
