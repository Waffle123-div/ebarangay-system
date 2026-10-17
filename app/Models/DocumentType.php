<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'document_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'fee',
        'processing_days',
        'requirements',
        'template_file',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fee'          => 'decimal:2',
        'requirements' => 'array',
        'is_active'    => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'icon',
        'formatted_fee',
        'requirements_list',
    ];

    /**
     * Helper to get a matching FontAwesome icon class for this document type.
     */
    public function getIconAttribute(): string
    {
        $nameLower = strtolower($this->name ?? '');

        if (str_contains($nameLower, 'business') || str_contains($nameLower, 'permit')) {
            return 'fa-store';
        }
        if (str_contains($nameLower, 'residency') || str_contains($nameLower, 'resident')) {
            return 'fa-house-chimney-user';
        }
        if (str_contains($nameLower, 'medical') || str_contains($nameLower, 'health') || str_contains($nameLower, 'hospital')) {
            return 'fa-kit-medical';
        }
        if (str_contains($nameLower, 'indigency') || str_contains($nameLower, 'indigent')) {
            return 'fa-hand-holding-heart';
        }
        if (str_contains($nameLower, 'cohabitation')) {
            return 'fa-people-roof';
        }
        if (str_contains($nameLower, 'bail') || str_contains($nameLower, 'court') || str_contains($nameLower, 'legal') || str_contains($nameLower, 'law')) {
            return 'fa-gavel';
        }
        if (str_contains($nameLower, 'electric') || str_contains($nameLower, 'power') || str_contains($nameLower, 'meralco')) {
            return 'fa-bolt';
        }
        if (str_contains($nameLower, 'burial') || str_contains($nameLower, 'death') || str_contains($nameLower, 'mortuary') || str_contains($nameLower, 'funeral')) {
            return 'fa-cross';
        }
        if (str_contains($nameLower, 'senior') && (str_contains($nameLower, 'cash') || str_contains($nameLower, 'pension') || str_contains($nameLower, 'money') || str_contains($nameLower, 'assistance') || str_contains($nameLower, 'subsidy'))) {
            return 'fa-hand-holding-dollar';
        }
        if (str_contains($nameLower, 'senior')) {
            return 'fa-user-tie';
        }
        if (str_contains($nameLower, 'job') || str_contains($nameLower, 'employment') || str_contains($nameLower, 'seeker')) {
            return 'fa-briefcase';
        }
        if (str_contains($nameLower, 'oath') || str_contains($nameLower, 'undertaking') || str_contains($nameLower, 'affidavit')) {
            return 'fa-file-signature';
        }
        if (str_contains($nameLower, 'clearance')) {
            return 'fa-file-shield';
        }
        if (str_contains($nameLower, 'scholarship') || str_contains($nameLower, 'school') || str_contains($nameLower, 'student')) {
            return 'fa-graduation-cap';
        }
        if (str_contains($nameLower, 'probation') || str_contains($nameLower, 'parole')) {
            return 'fa-clipboard-check';
        }
        if (str_contains($nameLower, 'receipt') || str_contains($nameLower, 'acknowledgement')) {
            return 'fa-receipt';
        }
        if (str_contains($nameLower, 'id') || str_contains($nameLower, 'identification') || str_contains($nameLower, 'card')) {
            return 'fa-id-card';
        }

        return 'fa-file-lines';
    }

    /**
     * Scope a query to only include active document types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Helper to get requirements as a clean array.
     */
    public function getRequirementsListAttribute(): array
    {
        if (is_array($this->requirements)) {
            return $this->requirements;
        }

        if (is_string($this->requirements)) {
            $decoded = json_decode($this->requirements, true);
            if (is_array($decoded)) {
                return $decoded;
            }
            return array_map('trim', explode(',', $this->requirements));
        }

        return [];
    }

    /**
     * Helper to get formatted fee (e.g. "₱50.00" or "FREE").
     */
    public function getFormattedFeeAttribute(): string
    {
        if ($this->fee === null || (float)$this->fee == 0) {
            return 'FREE';
        }

        return '₱' . number_format((float)$this->fee, 2);
    }

    /**
     * Relationship to document requests of this type.
     */
    public function documentRequests()
    {
        return $this->hasMany(DocumentRequest::class, 'document_type_id');
    }
}
