<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
{
    // 1. Inisialisasi Query Builder
    $query = Employee::with(['partnership', 'manager']);

    // 2. Logika Search (NIK atau Nama)
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('nik', 'like', "%{$search}%")
              ->orWhere('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%");
        });
    }

    // 3. Logika Filter Position & Division
    if ($request->filled('position')) {
        $query->where('position', $request->position);
    }
    if ($request->filled('division')) {
        $query->where('division', $request->division);
    }

   // 4. Logika Sorting
    $sortField = $request->input('sort', 'created_at'); 
    $sortOrder = $request->input('direction', 'desc'); 
    
    $allowedSorts = ['nik', 'first_name', 'position', 'division', 'date_of_entry'];
    if (in_array($sortField, $allowedSorts)) {
        // Jika sort by first_name, tambahkan fallback second order agar konsisten
        $query->orderBy($sortField, $sortOrder);
    } else {
        $query->orderBy('created_at', 'desc');
    }

    // 5. Pagination
    $perPage = min($request->input('per_page', 5), 25);
    $employees = $query->paginate($perPage)->withQueryString();

    // 6. Transformasi Data untuk View (Logika Avatar & Initials Anda)
    $tableData = collect($employees->items())->map(function ($emp) {
        $initials = strtoupper(substr($emp->first_name ?? 'E', 0, 1) . substr($emp->last_name ?? '', 0, 1));

        // Logika Avatar Anda
        $gender = $emp->gender;
        if ($gender === 'male') {
            $avatarBg = 'bg-blue-100';
            $avatarColor = 'text-blue-500';
        } elseif ($gender === 'female') {
            $avatarBg = 'bg-pink-100';
            $avatarColor = 'text-pink-500';
        } else {
            $avatarBg = 'bg-gray-100';
            $avatarColor = 'text-gray-500';
        }

        return [
            'id' => $emp->id,
            'nik' => $emp->nik,
            'employeeName' => trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')),
            'initials' => $initials ?: 'EE',
            'entry_date' => $emp->date_of_entry ? date('d M Y', strtotime($emp->date_of_entry)) : '-',
            'position' => $emp->position,
            'division' => $emp->division,
            'avatarBg' => $avatarBg,
            'avatarColor' => $avatarColor,
            'partnership' => $emp->partnership->name ?? '-',
            'manager' => $emp->manager ? trim($emp->manager->first_name . ' ' . $emp->manager->last_name) : 'No Manager',
        ];
    });

    // Ambil daftar unik untuk dropdown filter di view
    $positions = Employee::distinct()->whereNotNull('position')->pluck('position');
    $divisions = Employee::distinct()->whereNotNull('division')->pluck('division');

    return view('pages.hr.employee', [
        'tableData' => $tableData,
        'employees' => $employees,
        'positions' => $positions,
        'divisions' => $divisions
    ]);
}

    public function destroy($id)
{
    try {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return response()->json([
            'success' => true,
            'notification' => [
                'variant' => 'success',
                'title'   => 'Berhasil!',
                'message' => 'Data karyawan telah dihapus.'
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal menghapus data.'
        ], 500);
    }
}
}
