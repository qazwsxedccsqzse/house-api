<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Admin extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'integer',
    ];

    /**
     * Get the roles that belong to this admin.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'admin_roles');
    }

    /**
     * Get all permissions for this admin through roles.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'admin_roles', 'admin_id', 'permission_id')
            ->join('role_permissions', 'permissions.id', '=', 'role_permissions.permission_id')
            ->join('admin_roles as ar', 'role_permissions.role_id', '=', 'ar.role_id')
            ->where('ar.admin_id', $this->id);
    }

    /**
     * Scope a query to only include active admins.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Check if admin is active.
     */
    public function isActive(): bool
    {
        return $this->status === 1;
    }

    /**
     * Check if admin has a specific permission.
     */
    public function hasPermission(string $permissionCode): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionCode) {
                $query->where('code', $permissionCode);
            })
            ->exists();
    }

    /**
     * Check if admin has any of the given permissions.
     */
    public function hasAnyPermission(array $permissionCodes): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionCodes) {
                $query->whereIn('code', $permissionCodes);
            })
            ->exists();
    }

    /**
     * Check if admin has all of the given permissions.
     */
    public function hasAllPermissions(array $permissionCodes): bool
    {
        $adminPermissions = $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap(function ($role) {
                return $role->permissions;
            })
            ->pluck('code')
            ->unique()
            ->toArray();

        return count(array_intersect($permissionCodes, $adminPermissions)) === count($permissionCodes);
    }
}
