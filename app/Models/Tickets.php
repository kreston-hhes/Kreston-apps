<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tickets extends Model
{
    protected $table = 'tickets';

    protected $fillable = [
        'id_ticket',
        'request_date',
        'id_employee',
        'issue_description',
        'status',
        'assigned_to',
    ];

    protected $casts = [
        'request_date' => 'datetime',
    ];

    //relasi ke Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'id_employee');
    }
}
