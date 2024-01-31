<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ILLRequest extends Model
{
    use HasFactory;

    protected $table = 'ill_requests';

    protected $fillable = [
        'request_date',
        'fulfilled',
        'unfulfilled_reason',
        'resource',
        'action',
        'library',
        'requestor_type',
        'requestor_notes'
    ];
}
