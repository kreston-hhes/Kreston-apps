<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Partnership extends Model
{
    use HasFactory;

    protected $fillable = [
        'nik',
        'code',
        'name',
        'email',
        'phone',
        'address',
        'gender',
        'birth_date',
        'position',
        'division',
        'date_of_entry',
        'release_date',
        'status',
    ];  
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
