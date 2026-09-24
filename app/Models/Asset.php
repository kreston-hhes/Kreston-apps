<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'partnership_id',
        'type_id',
        'asset_code',
        'name',
        'serial_number',
        'specification',
        'purchase_date',
        'warranty_expired',
        'vendor',
        'purchased_by',
        'condition_status',
        'condition_notes',
        'qc_status',
        'qc_date',
        'qc_notes',
        'placement_status',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expired' => 'date',
        'qc_date' => 'date',
        'specification' => 'array',
    ];

    
    public function type(): BelongsTo
    {
        return $this->belongsTo(AssetType::class, 'type_id');
    }

    public function partnership(): BelongsTo
    {
        return $this->belongsTo(Partnership::class, 'partnership_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class, 'asset_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(AssetLoan::class, 'asset_id');
    }
    
    // Relasi untuk mengambil data peminjaman (logbook) terakhir
    public function latestLoan()
    {
        return $this->hasOne(AssetLoan::class, 'asset_id')->latestOfMany('borrowed_at');
    }

    public function consumables(): HasMany
    {
        return $this->hasMany(Consumable::class, 'asset_id');
    }

    public function printerBillings(): HasMany
    {
        return $this->hasMany(PrinterBilling::class, 'asset_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'asset_id');
    }

    /**
     * Baris assignment yang sedang aktif (belum returned_at) dipakai
     * buat kolom "Terakhir dipakai" / cek siapa pemakai sekarang.
     */
    public function currentAssignment()
    {
        return $this->hasOne(AssetAssignment::class, 'asset_id')
                    ->whereNull('returned_at')
                    ->latestOfMany('assigned_at');
    }
    
    /**
     * Assignment terakhir (baik yang masih aktif maupun sudah returned),
     * dipakai buat nampilin "Terakhir dipakai" di tab Aset di server room.
     */
    public function latestAssignment()
    {
        return $this->hasOne(AssetAssignment::class, 'asset_id')
                    ->latestOfMany('assigned_at');
    }

    public function scopeServerRoom($query)
    {
        return $query->where('placement_status', 'server_room');
    }

    public function scopeUsedByEmployee($query)
    {
        return $query->where('placement_status', 'used_by_employee');
    }

    public function scopePendingQc($query)
    {
        return $query->where('qc_status', 'pending');
    }
}
