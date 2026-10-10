<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = ['name', 'value'];

    /**
     * Sentinel cached in place of null so that missing settings do not
     * re-query the database on every read. See get() below.
     */
    private const MISSING = '__SKULI_SETTING_MISSING__';

    /**
     * Cache namespace for the current version. Bumped by flushCache() to
     * invalidate every cached setting at once without enumerating keys.
     */
    public static function cacheVersion(): string
    {
        return Cache::rememberForever(
            'skuli.setting.version',
            fn () => (string) config('skuli.cache.version', 'v1')
        );
    }

    public static function cacheKey(string $name): string
    {
        return 'skuli.setting.' . self::cacheVersion() . '.' . $name;
    }

    /**
     * Read a setting value. Returns null when the setting has never been
     * written. Never applies a default -- that is the helper's job.
     */
    public static function get(string $name): ?string
    {
        $ttl = (int) config('skuli.cache.ttl', 86400);

        $value = Cache::remember(self::cacheKey($name), $ttl, function () use ($name) {
            $row = self::query()->where('name', $name)->value('value');
            return $row === null ? self::MISSING : $row;
        });

        return $value === self::MISSING ? null : $value;
    }

    /**
     * Write a setting and clear its cache entry.
     */
    public static function set(string $name, ?string $value): void
    {
        self::query()->updateOrCreate(
            ['name' => $name],
            ['value' => $value]
        );

        Cache::forget(self::cacheKey($name));
    }

    /**
     * Delete a setting row and its cache entry.
     */
    public static function forget(string $name): void
    {
        self::query()->where('name', $name)->delete();
        Cache::forget(self::cacheKey($name));
    }

    /**
     * Invalidate every cached setting by bumping the version namespace.
     * Does not touch the database.
     */
    public static function flushCache(): void
    {
        Cache::forever('skuli.setting.version', 'v' . microtime(true));
    }
}
