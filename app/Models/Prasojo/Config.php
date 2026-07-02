<?php

declare(strict_types=1);

namespace App\Models\Prasojo;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Config
 *
 * @property int $config_id
 * @property string|null $config_name
 * @property string|null $config_value
 * @property bool $config_autoload
 */
final class Config extends Model
{
    protected $connection = 'mysql_prasojo';

    protected $table = 'config';

    protected $primaryKey = 'config_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'config_name',
        'config_value',
        'config_autoload',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'config_autoload' => 'boolean',
    ];

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to filter autoloaded configs.
     */
    public function scopeAutoload(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('config_autoload', true);
    }

    // ──────────────────────────────────────────────
    // Helper Methods
    // ──────────────────────────────────────────────

    /**
     * Get a config value by name.
     */
    public static function getValue(string $name, ?string $default = null): ?string
    {
        $config = static::where('config_name', $name)->first();

        return $config?->config_value ?? $default;
    }
}
