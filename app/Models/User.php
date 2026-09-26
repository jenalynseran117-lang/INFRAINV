<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relationship to roles
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    // Check a single role
    public function hasRole($role)
    {
        return $this->roles->contains('name', $role);
    }


    // Check multiple roles
    public function hasAnyRoles(array $roles)
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * The role this user is CURRENTLY acting as.
     *
     * A user can have more than one role attached (admin/inspector/supply).
     * When they log in, the login screen asks them to pick one, and
     * AuthenticatedSessionController stores that choice in the session as
     * 'active_role'. We prefer that value here so the whole app (dashboard
     * redirect, profile layout, sidebar, etc.) reflects the role the user
     * actually logged in as - not just whichever role happens to be first
     * in the role_user pivot table.
     *
     * Falls back to the first attached role (or 'Guest') for contexts
     * where there's no session yet, e.g. queued jobs or console commands.
     */
    public function getRoleAttribute()
    {
        $activeRole = session('active_role');

        if ($activeRole && $this->roles->contains('name', $activeRole)) {
            return $activeRole;
        }

        return $this->roles->first()?->name ?? 'Guest';
    }
}