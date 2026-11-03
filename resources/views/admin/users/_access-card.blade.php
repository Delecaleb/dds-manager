{{--
  Account access for one user on the edit page: where the account stands, and the email
  actions (resend invitation / send password reset) plus revoke or restore access.
  Expects $user. Each action is its own form, so this sits outside the main edit form.
--}}
@if (session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg text-xs flex items-center gap-2 shadow-sm">
        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif
@if (session('error'))
    <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-lg text-xs flex items-center gap-2 shadow-sm">
        <i data-lucide="alert-octagon" class="w-4 h-4 text-rose-600 shrink-0"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

<div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0
                {{ ! $user->is_active ? 'bg-rose-50 text-rose-600' : ($user->isInvitationPending() ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600') }}">
                <i data-lucide="{{ ! $user->is_active ? 'shield-off' : ($user->isInvitationPending() ? 'mail' : 'shield-check') }}" class="w-4 h-4"></i>
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Account access</h2>
                <p class="text-xs text-slate-600 mt-0.5">
                    @if (! $user->is_active)
                        Access revoked. {{ $user->email }} cannot sign in until access is restored.
                    @elseif ($user->isInvitationPending())
                        Invitation sent — waiting for {{ $user->email }} to choose a password.
                    @else
                        Active. Last signed in {{ $user->last_login_at?->diffForHumans() ?? 'never' }}.
                    @endif
                </p>
            </div>
        </div>

        @if ($user->id !== auth()->id())
            <div class="flex flex-wrap items-center gap-2">
                @if ($user->is_active && $user->isInvitationPending())
                    <form method="POST" action="{{ route('admin.users.resend-invite', $user) }}">
                        @csrf
                        <button type="submit" class="px-3 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold flex items-center gap-1.5">
                            <i data-lucide="send" class="w-3.5 h-3.5 text-blue-600"></i> Resend invitation
                        </button>
                    </form>
                @elseif ($user->is_active)
                    <form method="POST" action="{{ route('admin.users.password-reset', $user) }}"
                        onsubmit="return confirm(@js('Email '.$user->email.' a link to choose a new password? Their current password keeps working until they use it.'));">
                        @csrf
                        <button type="submit" class="px-3 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-semibold flex items-center gap-1.5">
                            <i data-lucide="key-round" class="w-3.5 h-3.5 text-blue-600"></i> Send password reset email
                        </button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}"
                    onsubmit="return confirm(@js($user->is_active ? 'Revoke access for '.$user->email.'? They are signed out immediately and cannot sign in.' : 'Restore access for '.$user->email.'?'));">
                    @csrf
                    @method('PATCH')
                    @if ($user->is_active)
                        <button type="submit" class="px-3 py-2 rounded-lg border border-amber-300 text-amber-700 hover:bg-amber-50 text-xs font-semibold flex items-center gap-1.5">
                            <i data-lucide="shield-off" class="w-3.5 h-3.5"></i> Revoke access
                        </button>
                    @else
                        <button type="submit" class="px-3 py-2 rounded-lg border border-emerald-300 text-emerald-700 hover:bg-emerald-50 text-xs font-semibold flex items-center gap-1.5">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Restore access
                        </button>
                    @endif
                </form>
            </div>
        @endif
    </div>
</div>
