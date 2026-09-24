<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetAssignment extends Model
{
    use SoftDeletes; // delete() cuma isi kolom deleted_at, row tetap ada di DB

protected $fillable = [
        'asset_id', 'employee_id', 'assigned_at',
        'returned_at', 'signed_document_path', 'assignment_reason', 'notes',
        'letter_type', 'letter_number', 'return_letter_number',
        'charger_included', 'battery_included', 'bag_included',
        'cleanliness_condition', 'physical_condition', 'charger_condition',
        'battery_condition', 'bag_condition', 'delegated_by_employee_id',
    ];

    // Assignment ini mengikat ke aset yang mana
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    // Assignment ini diserahkan ke karyawan siapa
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    /**
     * Nomor surat handover: 00x/IT/HO/{romawi bulan}/{tahun}, reset tiap bulan.
     * Ini satu-satunya sumber logic generate nomor — panggil dari controller
     * pakai AssetAssignment::generateLetterNumber(), jangan bikin versi duplikat lagi.
     */
    public static function generateLetterNumber(): string
    {
        $romawiBulan = [
            1=>'I', 2=>'II', 3=>'III', 4=>'IV', 5=>'V', 6=>'VI',
            7=>'VII', 8=>'VIII', 9=>'IX', 10=>'X', 11=>'XI', 12=>'XII',
        ];

        $bulan = now()->month;
        $tahun = now()->year;

        $urutan = self::whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->where('letter_type', 'handover')
            ->whereNotNull('letter_number')
            ->lockForUpdate()
            ->count() + 1;

        return sprintf('%03d/IT/HO/%s/%d', $urutan, $romawiBulan[$bulan], $tahun);
    }

    /**
     * Nomor surat pengembalian: 00x/IT/RT/{romawi bulan}/{tahun}, reset tiap
     * bulan, terpisah dari counter Surat Penyerahan (letter_number).
     */
    public static function generateReturnLetterNumber(): string
    {
        $romawiBulan = [
            1=>'I', 2=>'II', 3=>'III', 4=>'IV', 5=>'V', 6=>'VI',
            7=>'VII', 8=>'VIII', 9=>'IX', 10=>'X', 11=>'XI', 12=>'XII',
        ];

        $bulan = now()->month;
        $tahun = now()->year;

        $urutan = self::whereMonth('returned_at', $bulan)
            ->whereYear('returned_at', $tahun)
            ->whereNotNull('return_letter_number')
            ->lockForUpdate()
            ->count() + 1;

        return sprintf('%03d/IT/RT/%s/%d', $urutan, $romawiBulan[$bulan], $tahun);
    }
}