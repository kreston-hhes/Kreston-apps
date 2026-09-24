<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_code',
        'document_flow',
        'spec_template',
    ];

    protected $casts = [
        // Tanpa ini, Eloquent coba simpan array PHP mentah ke kolom json
        // dan gagal dengan error "Array to string conversion".
        'spec_template' => 'array',
    ];

    public function types(): HasMany
    {
        return $this->hasMany(AssetType::class, 'category_id');
    }
}
