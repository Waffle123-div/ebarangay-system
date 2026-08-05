<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'username',
        'email',
        'phone_number',
        'password',
        'google_id',
        'role',
        'status',

        // Personal info
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'birthdate',
        'gender',
        'civil_status',
        'nationality',
        'occupation',

        // Profile picture
        'profile_picture',

        // Address
        'house_number',
        'street',
        'barangay_address',
        'municipality',
        'province',
        'zip_code',

        // Valid ID
        'id_type',
        'id_photo',
        'id_expiry_date',
        'id_verified_at',
        'id_uploaded_at',

        // Proof of residency (legacy)
        'proof_of_residency',

        // Security
        'two_factor_enabled',
        'security_pin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'security_pin',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'two_factor_enabled' => 'boolean',
            'id_expiry_date'    => 'date',
            'id_verified_at'    => 'datetime',
            'id_uploaded_at'    => 'datetime',
        ];
    }

    /**
     * Activity log entries for this user.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class)->latest();
    }

    public function communityConcerns()
    {
        return $this->hasMany(CommunityConcern::class)->latest();
    }

    public function formattedAddress(): string
    {
        return collect([
            $this->house_number,
            $this->street,
            $this->barangay_address,
            $this->municipality,
            $this->province,
            $this->zip_code,
        ])->filter()->implode(', ');
    }
}
