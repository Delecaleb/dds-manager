<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Users\UserAccessService;
use App\Support\ModuleManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

/**
 * Super-admin user management. Admins set who a user is and what they may open; they
 * never choose or see a password. New users are invited by email and choose their own;
 * forgotten passwords are reset by email. See UserAccessService.
 */
class AdminUserController extends Controller
{
    public function __construct(private readonly UserAccessService $access) {}

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

        if ($statusFilter === 'pending') {
            $query->whereNull('email_verified_at')->whereNull('last_login_at');
        } elseif ($statusFilter !== null && $statusFilter !== '') {
            $query->where('is_active', (bool) (int) $statusFilter);
        }

        $users = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => User::count(),
            'super_admins' => User::where('role', 'super_admin')->count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'pending' => User::whereNull('email_verified_at')->whereNull('last_login_at')->count(),
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
     * Show the form for inviting a new user.
     */
    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => ModuleManager::ROLES,
            'categories' => ModuleManager::getCategories(),
            'allModules' => ModuleManager::all(),
            'inviteDays' => $this->access->inviteDays(),
        ]);
    }

    /**
     * Create the account from an email and its permissions, and email the invitation.
     * The user chooses their own password (and confirms their name) from that email.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'name' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'string', Rule::in(array_keys(ModuleManager::ROLES))],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', Rule::in(ModuleManager::keys())],
        ]);

        $user = User::create([
            // Until they accept, the account is named from the email; they confirm their name then.
            'name' => filled($validated['name'] ?? null) ? $validated['name'] : Str::of($validated['email'])->before('@')->replace(['.', '_', '-'], ' ')->title()->value(),
            'email' => $validated['email'],
            'password' => $this->access->unusablePassword(),
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        if ($user->role !== 'super_admin') {
            $user->syncModules($request->input('modules', []));
        }

        if (! $this->sendInvite($user, $request->user())) {
            return redirect()->route('admin.users.index')
                ->with('error', "The account for {$user->email} was created, but the invitation email could not be sent. Check the mail settings, then use \"Resend invitation\".");
        }

        return redirect()->route('admin.users.index')
            ->with('success', "Invitation sent to {$user->email}. They choose their own password from the email (the link works for {$this->access->inviteDays()} days).");
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
     * Update the specified user's details, role and modules. Passwords are not set here:
     * use "Send password reset email".
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
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

        $wasActive = $user->is_active;

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->is_active = $request->boolean('is_active', true);
        $user->save();

        // Unticking "active" is the same as revoking: end their open sessions now.
        if ($wasActive && ! $user->is_active) {
            $this->access->endSessions($user);
        }

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
     * Revoke access (deactivate and sign out everywhere), or restore it.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot revoke your own access.');
        }

        if ($user->is_active) {
            $this->access->revoke($user);

            return back()->with('success', "Access revoked for {$user->email}. They have been signed out and cannot sign in until access is restored.");
        }

        $this->access->restore($user);

        return back()->with('success', "Access restored for {$user->email}. They can sign in again with their existing password.");
    }

    /**
     * Email a fresh invitation link (any earlier link stops working).
     */
    public function resendInvite(Request $request, User $user): RedirectResponse
    {
        if (! $user->isInvitationPending()) {
            return back()->with('error', "{$user->email} has already set a password. Send a password reset email instead.");
        }

        if (! $user->is_active) {
            return back()->with('error', "Restore access for {$user->email} before sending a new invitation.");
        }

        return $this->sendInvite($user, $request->user())
            ? back()->with('success', "A new invitation was sent to {$user->email}. Earlier invitation links no longer work.")
            : back()->with('error', "The invitation email to {$user->email} could not be sent. Check the mail settings and try again.");
    }

    /**
     * Email the user a link to choose a new password. The admin never sees it.
     */
    public function sendPasswordReset(User $user): RedirectResponse
    {
        if (! $user->is_active) {
            return back()->with('error', "{$user->email} has no access. Restore access first, then send the reset email.");
        }

        if ($user->isInvitationPending()) {
            return back()->with('error', "{$user->email} has not accepted their invitation yet. Resend the invitation instead.");
        }

        try {
            $status = $this->access->sendPasswordReset($user);
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', "The password reset email to {$user->email} could not be sent. Check the mail settings and try again.");
        }

        return match ($status) {
            Password::RESET_LINK_SENT => back()->with('success', "Password reset email sent to {$user->email}. The link works for 60 minutes."),
            Password::RESET_THROTTLED => back()->with('error', "A reset email was sent to {$user->email} moments ago. Wait a minute before sending another."),
            default => back()->with('error', __($status)),
        };
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
        $this->access->endSessions($user);
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$userName}' deleted successfully.");
    }

    /** Send the invitation; false (and the error reported) when mail could not be sent. */
    private function sendInvite(User $user, User $invitedBy): bool
    {
        try {
            $this->access->invite($user, $invitedBy);

            return true;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }
}
