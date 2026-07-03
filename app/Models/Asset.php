<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
    'partnership_id',
    'type_id',
    'asset_code',
    'name',
    'serial_number',
    'specification',
    'purchase_date',
    'warranty_expired',
    'condition_status',
    'placement_status'
    ];

    public function partnership()
    {
        return $this->belongsTo(Partnership::class, 'partnership_id');
    }

    public function type()
    {
        return $this->belongsTo(AssetType::class, 'type_id');
    }

    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class, 'asset_id');
    }

    public function currentAssignment()
    {
        return $this->hasOne(AssetAssignment::class, 'asset_id')->whereNull('returned_at');
    }
}
