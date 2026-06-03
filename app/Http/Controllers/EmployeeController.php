<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Partnership;
use Illuminate\Http\Request;

class EmployeeController extends Controller {
    public function index(Request $request) {
        // 1. Inisialisasi Query Builder
        $query = Employee::with(['partnership', 'manager'])
        ->where(function ($q) { 
                // memastikan yang statusnya 'deleted' tidak ikut terbawa
                $q->where('status', '!=', 'deleted')->orWhereNull('status');
            });

        // 2. Logika Search (NIK atau Nama)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        // 3. Logika Filter Position & Division & Partnership
        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }
        if ($request->filled('division')) {
            $query->where('division', $request->division);
        }
        if ($request->filled('partnership')) {
            $query->whereHas('partnership', function ($q) use ($request) {
                $q->where('name', $request->partnership);
            });
        }

        // 4. Logika Sorting
        $sortField = $request->input('sort', 'created_at');
        $sortOrder = $request->input('direction', 'desc');

        $allowedSorts = ['nik', 'first_name', 'position', 'division', 'date_of_entry'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // 5. Pagination
        $perPage   = min($request->input('per_page', 5), 25);
        $employees = $query->paginate($perPage)->withQueryString();
        
        $activeEmployees = collect($employees->items())->filter(function ($emp) {
        return $emp->status !== 'resigned';
        });
        $resignedEmployees = collect($employees->items())->filter(function ($emp) {
        return $emp->status === 'resigned';
        });
        // 6. Transformasi Data untuk View 
        $activeData   = $activeEmployees->map(fn ($emp) => $this->toTableRow($emp))->values();
        $resignedData = $resignedEmployees->map(fn ($emp) => $this->toTableRow($emp))->values();

        // 7. Dropdown filter untuk tabel atas 
        $positions    = Employee::distinct()->whereNotNull('position')->orderBy('position')->pluck('position');
        $divisions    = Employee::distinct()->whereNotNull('division')->orderBy('division')->pluck('division');
        $partnerships = Employee::distinct()->whereNotNull('partnership_id')->orderBy('partnership_id')
            ->pluck('partnership_id')
            ->map(fn ($id) => optional(Partnership::find($id))->name)
            ->filter()->unique()->values();

        // 8. Daftar untuk dropdown di dalam Modal Form 
        $managers = Employee::where('position', 'LIKE', '%Manager%') 
            ->where(function ($q) {
                $q->where('status', '!=', 'deleted')->orWhereNull('status');
            })
            ->where('status', '!=', 'resigned') 
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);
        $partnershipOptions = Partnership::orderBy('name')->get(['id', 'name']); 
        $totalActive = \App\Models\Employee::where('status', 'active')->count(); 
        $totalResigned = \App\Models\Employee::where('status', 'resigned')->count();

    return view('pages.hr.employee', [
        'activeData'         => $activeData,
        'resignedData'       => $resignedData,
        'employees'          => $employees,
        'positions'          => $positions,
        'divisions'          => $divisions,
        'partnerships'       => $partnerships,
        'partnershipOptions' => $partnershipOptions, 
        'managers'           => $managers,          
        'totalActive'        => $totalActive,
        'totalResigned'      => $totalResigned,
    ]);
    }

    // STORE CREATE
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik'            => 'required|string|max:20|unique:employees,nik',
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'nullable|string|max:100',
            'gender'         => 'nullable|in:male,female',
            'position'       => 'nullable|string|max:100',
            'division'       => 'nullable|string|max:100',
            'partnership_id' => 'nullable|exists:partnerships,id',
            'manager_id'     => 'nullable|exists:employees,id',
            'date_of_entry'  => 'required|date|before_or_equal:today',
        ]);

        $employee = Employee::create($validated);
        $employee->load(['partnership', 'manager']);

        return response()->json([
            'success'  => true,
            'message'  => trim("{$employee->first_name} {$employee->last_name}") . ' berhasil ditambahkan.',
            'employee' => $this->toTableRow($employee),
        ], 201);
    }

    // UPDATE EDIT DATA
    public function update(Request $request, int $id)
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'nik'            => "required|string|max:20|unique:employees,nik,{$employee->id}",
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'nullable|string|max:100',
            'gender'         => 'nullable|in:male,female',
            'position'       => 'nullable|string|max:100',
            'division'       => 'nullable|string|max:100',
            'partnership_id' => 'nullable|exists:partnerships,id',
            'manager_id'     => 'nullable|exists:employees,id',
            'date_of_entry'  => 'required|date|before_or_equal:today',
        ]);

        // agar karyawan tidak jadi manager dirinya sendiri
        if (!empty($validated['manager_id']) && $validated['manager_id'] == $employee->id) {
            return response()->json([
                'success' => false,
                'errors'  => ['manager_id' => ['Karyawan tidak bisa menjadi manager dirinya sendiri.']],
            ], 422);
        }

        $employee->update($validated);
        $employee->load(['partnership', 'manager']);

        return response()->json([
            'success'  => true,
            'message'  => trim("{$employee->first_name} {$employee->last_name}") . ' berhasil diperbarui.',
            'employee' => $this->toTableRow($employee),
        ]);
    }

    // PROSES RESIGN
    public function resign(Request $request, int $id)
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'release_date' => [
                'required',
                'date',
                'before_or_equal:today', // maksimal hari ini
                'after_or_equal:' . $employee->date_of_entry // minimal sama dengan tanggal masuknya
            ],
        ], [
            'release_date.before_or_equal' => 'Tanggal resign tidak boleh melebihi hari ini.',
            'release_date.after_or_equal'  => 'Tanggal resign harus setelah tanggal masuk kerja (' . date('d M Y', strtotime($employee->date_of_entry)) . ').'
        ]);

        try {
            $employee = Employee::findOrFail($id);
            
            $employee->update([
                'status'       => 'resigned',
                'release_date' => $validated['release_date'], 
            ]);

            $employee->load(['partnership', 'manager']);

            return response()->json([
                'success'  => true,
                'message'  => trim("{$employee->first_name} {$employee->last_name}") . ' berhasil ditandai sebagai resign.',
                'employee' => $this->toTableRow($employee),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    // DESTROY/DELETE
    public function destroy(int $id)
    {
        try {
            $employee = Employee::findOrFail($id);
            $employee->update(['status' => 'deleted']);

            return response()->json([
                'success'      => true,
                'notification' => [
                    'variant' => 'success',
                    'title'   => 'Berhasil!',
                    'message' => 'Data karyawan telah dihapus.',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data.',
            ], 500);
        }
    }

    // PRIVATE HELPER
    private function toTableRow(Employee $emp): array
    {
        $initials = strtoupper(
            substr($emp->first_name ?? 'E', 0, 1) .
            substr($emp->last_name  ?? '',  0, 1)
        );

        if ($emp->gender === 'male') {
            $avatarBg    = 'bg-blue-100';
            $avatarColor = 'text-blue-500';
        } elseif ($emp->gender === 'female') {
            $avatarBg    = 'bg-pink-100';
            $avatarColor = 'text-pink-500';
        } else {
            $avatarBg    = 'bg-gray-100';
            $avatarColor = 'text-gray-500';
        }

        return [
            'id'                => $emp->id,
            'nik'               => $emp->nik,
            'first_name'        => $emp->first_name,
            'last_name'         => $emp->last_name    ?? '',
            'gender'            => $emp->gender       ?? '',
            'employeeName'      => trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')),
            'initials'          => $initials ?: 'EE',
            'avatarBg'          => $avatarBg,
            'avatarColor'       => $avatarColor,
            'position'          => $emp->position     ?? '',
            'division'          => $emp->division     ?? '',
            'partnership'       => $emp->partnership->name ?? '-',
            'partnership_id'    => $emp->partnership_id,
            'manager'           => $emp->manager
                                    ? trim($emp->manager->first_name . ' ' . $emp->manager->last_name)
                                    : 'No Manager',
            'manager_id'        => $emp->manager_id,
            'entry_date'        => $emp->date_of_entry
                                    ? date('d M Y', strtotime($emp->date_of_entry))
                                    : '-',
            'date_of_entry_raw' => $emp->date_of_entry
                                    ? date('Y-m-d', strtotime($emp->date_of_entry))
                                    : '',
            'release_date'      => $emp->release_date 
                                    ? date('d M Y', strtotime($emp->release_date))
                                    : '-',
        ];
    }
}