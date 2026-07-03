<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetType extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'type_code',
    ];

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'type_id');
    }
}
