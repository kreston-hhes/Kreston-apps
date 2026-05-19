<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tickets extends Model
{
    protected $table = 'tickets';

    protected $fillable = [
        'id_ticket',
        'request_date',
        'requester_name',
        'requester_email',
        'partner_name',
        'phone_number',
        'issue_description',
        'resolution',
        'status',
        'assigned_to',
    ];

    protected $casts = [
        'request_date' => 'datetime',
    ];

  
}
