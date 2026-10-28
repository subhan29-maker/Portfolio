<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A key/value setting that has no table of its own.
 *
 * Today this holds the profile picture path and its alt text, so both survive
 * deploys and can be edited from the dashboard instead of source code.
 */
class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = ['key', 'value'];

    /**
     * Read a raw stored value.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $value = static::query()->where('key', $key)->value('value');

        return $value === null ? $default : (string) $value;
    }

    /**
     * Store a value (or clear it when null) in a single query.
     */
    public static function put(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );
    }

    /**
     * Forget a stored value entirely.
     */
    public static function forget(string $key): void
    {
        static::query()->where('key', $key)->delete();
    }
}
