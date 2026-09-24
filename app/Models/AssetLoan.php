<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetLoan extends Model
{
    // Tambahkan 'team_division' di sini
    protected $fillable = [
        'asset_id', 'pic_name', 'partnership_id',
        'duration_note', 'borrowed_at', 'returned_at', 'team_division'
    ];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function partnership()
    {
        return $this->belongsTo(Partnership::class, 'partnership_id');
    }
}