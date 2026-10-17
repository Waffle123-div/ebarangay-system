<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
    ];

    /**
     * Get the user that owns this activity log entry.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Convenient static helper to record an activity log entry.
     *
     * @param  string       $action       Short action name (e.g. 'request_approved')
     * @param  string       $description  Human-readable description
     * @param  int|null     $userId       User ID (defaults to currently authenticated user)
     */
    public static function record(string $action, string $description, ?int $userId = null): void
    {
        $resolvedUserId = $userId ?? Auth::id();
        if (!$resolvedUserId) {
            $resolvedUserId = User::where('role', 'Admin')->value('id');
        }
        if (!$resolvedUserId) {
            return;
        }

        try {
            static::create([
                'user_id'    => $resolvedUserId,
                'action'     => $action,
                'description'=> $description,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("ActivityLog recording skipped: " . $e->getMessage());
        }
    }
}
