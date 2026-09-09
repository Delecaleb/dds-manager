<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\ModuleManager;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get the module permissions assigned to this user.
     *
     * @return HasMany<UserModule, $this>
     */
    public function modules(): HasMany
    {
        return $this->hasMany(UserModule::class);
    }

    /**
     * Determine if the user has the Super Admin role.
     */
    public function isSuperAdmin(): bool
    {
        return strcasecmp((string) $this->role, 'super_admin') === 0;
    }

    /**
     * Check if the user has access to a specific module.
     */
    public function hasModuleAccess(string $moduleKey): bool
    {
        // Inactive users have no access
        if (! $this->is_active) {
            return false;
        }

        // Super Admin has unrestricted access to all modules
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->relationLoaded('modules')) {
            return $this->modules->contains('module_key', $moduleKey);
        }

        return $this->modules()->where('module_key', $moduleKey)->exists();
    }

    /**
     * Synchronize the user's module permissions.
     *
     * @param  list<string>  $moduleKeys
     */
    public function syncModules(array $moduleKeys): void
    {
        $validKeys = array_values(array_filter($moduleKeys, fn ($key) => ModuleManager::isValid($key)));

        $this->modules()->whereNotIn('module_key', $validKeys)->delete();

        foreach ($validKeys as $key) {
            $this->modules()->firstOrCreate(['module_key' => $key]);
        }

        $this->unsetRelation('modules');
    }

    /**
     * Get the list of accessible module keys for this user.
     *
     * @return list<string>
     */
    public function getAccessibleModuleKeys(): array
    {
        if ($this->isSuperAdmin()) {
            return ModuleManager::keys();
        }

        return $this->modules()->pluck('module_key')->all();
    }

    /**
     * Get human-readable role name.
     */
    public function getRoleName(): string
    {
        return ModuleManager::ROLES[$this->role]['name'] ?? ucfirst(str_replace('_', ' ', (string) $this->role));
    }

    /**
     * Get badge CSS classes for this user's role.
     */
    public function getRoleBadgeClass(): string
    {
        return ModuleManager::ROLES[$this->role]['badge_color'] ?? 'bg-slate-100 text-slate-700 border-slate-200';
    }
}
