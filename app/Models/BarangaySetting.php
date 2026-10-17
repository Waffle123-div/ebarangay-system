<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangaySetting extends Model
{
    use HasFactory;

    protected $table = 'barangay_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * Get a setting by key with an optional default.
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return ($setting && $setting->value !== null && $setting->value !== '') ? $setting->value : $default;
    }

    /**
     * Set/update a setting by key.
     */
    public static function set(string $key, $value, string $group = 'general'): self
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }

    /**
     * Get all settings as a key => value array.
     */
    public static function allAssoc(): array
    {
        return static::pluck('value', 'key')->toArray();
    }

    /**
     * Get settings belonging to a specific group as key => value array.
     */
    public static function getGroup(string $group): array
    {
        return static::where('group', $group)->pluck('value', 'key')->toArray();
    }
}
