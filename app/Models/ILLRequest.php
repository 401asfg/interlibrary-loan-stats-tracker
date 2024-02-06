<?php

// FIXME: should column names be stored in constants?

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ILLRequest extends Model
{
    use HasFactory;

    public const ACTIONS = [
        'borrow' => 'Borrow',
        'lend' => 'Lend',
        'send-to-me' => 'Send to Me'
    ];

    public const REQUESTOR_TYPES = [
        'student' => 'Student',
        'employee' => 'Employee',
        'external' => 'External',
        'library' => 'Library'
    ];

    public const UNFULFILLED_REASONS = [
        'unavailable' => 'Unavailable',
        'google-scholar' => 'Google Scholar',
        'other-language' => 'Other Language',
        'not-needed-after-date' => 'Not Needed After Date',
        'fulfilled-from-collection' => 'Fulfilled from Collection'
    ];

    public const RESOURCES = [
        'book' => 'Book',
        'ea' => 'EA',
        'book-chapter' => 'Book Chapter'
    ];

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
