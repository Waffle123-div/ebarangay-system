<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityConcernRemark extends Model
{
    protected $fillable = [
        'community_concern_id',
        'admin_id',
        'remark',
    ];

    public function concern(): BelongsTo
    {
        return $this->belongsTo(CommunityConcern::class, 'community_concern_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
