<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityConcern extends Model
{
    protected $fillable = [
        'concern_id',
        'user_id',
        'resident_name',
        'resident_email',
        'contact_number',
        'address',
        'title',
        'category',
        'description',
        'location',
        'incident_date',
        'photo_path',
        'status',
        'admin_remarks',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'incident_date' => 'date',
        ];
    }

    public static function categories(): array
    {
        return [
            'Infrastructure',
            'Road Damage',
            'Street Lighting',
            'Drainage',
            'Garbage Collection',
            'Public Cleanliness',
            'Water Supply',
            'Illegal Dumping',
            'Noise Complaint',
            'Stray Animals',
            'Public Safety',
            'Others',
        ];
    }

    public static function statuses(): array
    {
        return [
            'Pending',
            'Under Review',
            'In Progress',
            'Resolved',
            'Rejected',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function updatedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function remarks(): HasMany
    {
        return $this->hasMany(CommunityConcernRemark::class)->latest();
    }
}
