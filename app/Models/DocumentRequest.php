<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentRequest extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'tracking_number',
        'last_name',
        'first_name',
        'middle_initial',
        'contact_number',
        'resident_email',
        'purok',
        'street',
        'city',
        'age',
        'document_type',
        'priority',
        'purpose',
        'status',
        'is_generated',
        'assigned_staff',
    ];
}