<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'nik',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'gender',
        'birth_date',
        'position',
        'division',
        'date_of_entry',
        'release_date',
        'partnership_id',
        'manager_id',
        'user_id',
        'status',
    ];
// Relasi ke Partnership
    public function partnership()
    {
    return $this->belongsTo(Partnership::class);
    }

    // Relasi untuk mendapatkan data Manager
    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

// Relasi untuk mendapatkan daftar bawahan (Staff)
    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

}
