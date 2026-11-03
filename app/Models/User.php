<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\ModuleManager;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Route;

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
     * Invited but has not set a password yet. Accepting the invitation verifies the email;
     * last_login_at keeps accounts created before invitations (some never verified) from
     * reading as pending.
     */
    public function isInvitationPending(): bool
    {
        return $this->email_verified_at === null && $this->last_login_at === null;
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

        return $this->holdsPermission($moduleKey);
    }

    /**
     * Check if the user may open a page of a module that is split into pages
     * (ModuleManager `pages`). Modules without pages need only the module.
     */
    public function hasPageAccess(string $module, string $page): bool
    {
        if (! $this->hasModuleAccess($module)) {
            return false;
        }

        return $this->isSuperAdmin()
            || ModuleManager::pages($module) === []
            || $this->holdsPermission(ModuleManager::pageKey($module, $page));
    }

    /**
     * Check if the user may open a named route of a module. In a module split into pages,
     * a route no page covers (and not open) is refused, so unmapped routes stay closed.
     */
    public function canOpenRoute(string $module, ?string $routeName): bool
    {
        if (! $this->hasModuleAccess($module)) {
            return false;
        }

        if ($this->isSuperAdmin() || ModuleManager::pages($module) === []) {
            return true;
        }

        if ($routeName === null) {
            return false;
        }

        if (ModuleManager::isOpenRoute($module, $routeName)) {
            return true;
        }

        $page = ModuleManager::pageForRoute($module, $routeName);

        return $page !== null && $this->holdsPermission(ModuleManager::pageKey($module, $page));
    }

    /**
     * Whether a permission row exists. Loads all of the user's permissions once, since a
     * page (e.g. a nav) checks many.
     */
    private function holdsPermission(string $key): bool
    {
        return $this->loadMissing('modules')->modules->contains('module_key', $key);
    }

    /**
     * Synchronize the user's module and page permissions. A page permission is kept only
     * with its module, so unticking a module also removes its pages.
     *
     * @param  list<string>  $moduleKeys
     */
    public function syncModules(array $moduleKeys): void
    {
        $validKeys = array_values(array_filter($moduleKeys, function ($key) use ($moduleKeys) {
            $module = ModuleManager::moduleOfPage($key);

            return ModuleManager::isValid($key) && ($module === null || in_array($module, $moduleKeys, true));
        }));

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
     * Route name of the user's landing page: the first module they can access, in
     * ModuleManager order (so Dashboard wins when permitted). Users with no modules
     * land on their profile, the only page that needs no module permission.
     */
    public function homeRoute(): string
    {
        $accessible = $this->is_active ? $this->getAccessibleModuleKeys() : [];

        foreach (ModuleManager::all() as $key => $meta) {
            if (in_array($key, $accessible, true) && Route::has($meta['route'])) {
                return $meta['route'];
            }
        }

        return 'profile.edit';
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
