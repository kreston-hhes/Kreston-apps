<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetAssignment extends Model
{
 protected $fillable = [
        'asset_id', 'employee_id', 'assigned_at',
        'returned_at', 'signed_document_path', 'assignment_reason', 'notes'
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
}
