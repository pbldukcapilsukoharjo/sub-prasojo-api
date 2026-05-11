<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\AdminRole
 *
 * @property int $admin_role_id
 * @property string|null $admin_role_name
 * @property array|null $admin_role_access
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Admin> $admins
 */
final class AdminRole extends Model
{
    protected $table = 'admin_role';

    protected $primaryKey = 'admin_role_id';

    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'admin_role_name',
        'admin_role_access',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'admin_role_access' => 'array',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function admins(): HasMany
    {
        return $this->hasMany(Admin::class, 'role_id', 'admin_role_id');
    }

    // ──────────────────────────────────────────────
    // Helper Methods
    // ──────────────────────────────────────────────

    /**
     * Check if this role has a specific access permission.
     */
    public function hasAccess(string $permission): bool
    {
        return in_array($permission, $this->admin_role_access ?? [], true);
    }

    /**
     * Get the access list, guaranteed as an array.
     *
     * @return array<int, string>
     */
    public function getAccessList(): array
    {
        return $this->admin_role_access ?? [];
    }
}
