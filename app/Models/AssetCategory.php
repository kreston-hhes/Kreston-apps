<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    protected $fillable = [
        'name',
        'category_code',
    ];

    public function types()
    {
        return $this->hasMany(AssetType::class, 'category_id');
    }
}
