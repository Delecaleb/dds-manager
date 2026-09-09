<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ModuleManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    /**
     * Display a listing of all users and access statistics.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $roleFilter = $request->input('role');
        $statusFilter = $request->input('status');

        $query = User::with('modules')->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleFilter && array_key_exists($roleFilter, ModuleManager::ROLES)) {
            $query->where('role', $roleFilter);
        }

        if ($statusFilter !== null && $statusFilter !== '') {
            $query->where('is_active', (bool) (int) $statusFilter);
        }

        $users = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => User::count(),
            'super_admins' => User::where('role', 'super_admin')->count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
        ];

        return view('admin.users.index', [
            'users' => $users,
            'stats' => $stats,
            'roles' => ModuleManager::ROLES,
            'categories' => ModuleManager::getCategories(),
            'allModules' => ModuleManager::all(),
            'search' => $search,
            'roleFilter' => $roleFilter,
            'statusFilter' => $statusFilter,
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => ModuleManager::ROLES,
            'categories' => ModuleManager::getCategories(),
            'allModules' => ModuleManager::all(),
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', Rule::in(array_keys(ModuleManager::ROLES))],
            'is_active' => ['nullable', 'boolean'],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', Rule::in(ModuleManager::keys())],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active', true),
            'email_verified_at' => now(),
        ]);

        if ($user->role !== 'super_admin') {
            $user->syncModules($request->input('modules', []));
        }

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' created successfully with assigned access privileges.");
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $user->load('modules');

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => ModuleManager::ROLES,
            'categories' => ModuleManager::getCategories(),
            'allModules' => ModuleManager::all(),
            'assignedModuleKeys' => $user->modules->pluck('module_key')->all(),
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string', Rule::in(array_keys(ModuleManager::ROLES))],
            'is_active' => ['nullable', 'boolean'],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', Rule::in(ModuleManager::keys())],
        ]);

        // Safety Guard: Cannot demote oneself from Super Admin
        if ($user->id === auth()->id() && $validated['role'] !== 'super_admin') {
            return back()->withErrors(['role' => 'You cannot remove Super Admin privileges from your own account.'])->withInput();
        }

        // Safety Guard: Cannot deactivate own account
        if ($user->id === auth()->id() && ! $request->boolean('is_active', true)) {
            return back()->withErrors(['is_active' => 'You cannot deactivate your own account.'])->withInput();
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->is_active = $request->boolean('is_active', true);

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if ($user->role !== 'super_admin') {
            $user->syncModules($request->input('modules', []));
        } else {
            // Super admins have access to all modules automatically
            $user->modules()->delete();
        }

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' updated successfully.");
    }

    /**
     * Toggle the active status of a user.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "User '{$user->name}' has been {$statusText}.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->isSuperAdmin() && User::where('role', 'super_admin')->count() <= 1) {
            return back()->with('error', 'Cannot delete the only remaining Super Administrator account.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$userName}' deleted successfully.");
    }
}
