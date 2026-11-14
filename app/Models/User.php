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
        'email_verified_at',
        'phone_number',
        'password',
        'security_pin',
        'google_id',
        'role',
        'status',

        // Personal info
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'birthdate',
        'age',
        'birthplace',
        'gender',
        'civil_status',
        'nationality',
        'occupation',

        // Profile picture
        'profile_picture',

        // Address
        'purok',
        'sitio',
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

        // Face Verification
        'selfie_photo',
        'face_verified_at',

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
            'face_verified_at'  => 'datetime',
        ];
    }

    /**
     * Activity log entries for this user.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class)->latest();
    }

    /**
     * Document requests submitted by this user.
     */
    public function documentRequests()
    {
        return $this->hasMany(DocumentRequest::class, 'user_id')->latest();
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

    /**
     * Get the ID document path (resolving proof_of_residency, id_photo, or id_front_path).
     */
    public function getIdFrontPathAttribute(): ?string
    {
        return $this->proof_of_residency ?: ($this->id_photo ?: ($this->attributes['id_front_path'] ?? null));
    }

    /**
     * Get the URL to view the ID document.
     */
    public function getIdDocumentUrlAttribute(): ?string
    {
        $path = $this->id_front_path;
        if (!$path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL) || str_starts_with($path, 'data:image/')) {
            return $path;
        }

        if ($this->exists && \Illuminate\Support\Facades\Route::has('admin.resident.id_document')) {
            return route('admin.resident.id_document', [
                'user' => $this->getKey(),
                'filename' => basename(str_replace('\\', '/', $path)),
            ]);
        }

        return \Illuminate\Support\Facades\Storage::disk(\App\Services\StoredFileService::uploadDisk('public'))->url($path);
    }

    /**
     * Get the URL to view the selfie / face verification photo.
     */
    public function getSelfiePhotoUrlAttribute(): ?string
    {
        $path = $this->selfie_photo;
        if (!$path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL) || str_starts_with($path, 'data:image/')) {
            return $path;
        }

        if (\Illuminate\Support\Facades\Route::has('admin.resident.selfie')) {
            return route('admin.resident.selfie', basename($path));
        }

        return \Illuminate\Support\Facades\Storage::disk(\App\Services\StoredFileService::uploadDisk('public'))->url($path);
    }

    /**
     * Get the URL to view the user's profile picture without relying on public/storage symlinks.
     */
    public function getProfilePictureUrlAttribute(): ?string
    {
        $path = $this->profile_picture;
        if (!$path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL) || str_starts_with($path, 'data:image/')) {
            return $path;
        }

        $safeBase = basename(str_replace('\\', '/', $path));

        if (trim(strtolower((string) $this->role)) === 'admin' && \Illuminate\Support\Facades\Route::has('admin.profile.avatar')) {
            return route('admin.profile.avatar', ['filename' => $safeBase]);
        }

        if (\Illuminate\Support\Facades\Route::has('media.profile_avatar')) {
            return route('media.profile_avatar', ['filename' => $safeBase]);
        }

        return \Illuminate\Support\Facades\Storage::disk(\App\Services\StoredFileService::uploadDisk('public'))->url($path);
    }
}
