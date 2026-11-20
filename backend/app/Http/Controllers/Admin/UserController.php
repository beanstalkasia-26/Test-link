<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Responses\PageResponse;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return PageResponse::render('admin/users/Index', [
            'users' => User::query()->with('roles')->orderBy('name')->get()->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roleLabel(),
                'is_active' => $user->is_active,
                'two_factor' => $user->hasConfirmedTwoFactor(),
                'last_login_at' => $user->last_login_at?->diffForHumans() ?? 'Never',
            ]),
        ]);
    }

    public function create(): Response
    {
        return PageResponse::render('admin/users/Form', ['user' => null, 'roles' => $this->roles()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
            'role' => ['required', Rule::enum(Role::class)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => Str::lower($validated['email']),
            'password' => Str::password(40),
            'is_active' => true,
        ]);
        $user->syncRoles([$validated['role']]);
        Password::broker()->sendResetLink(['email' => $user->email]);
        AuditLogger::log('user.created', $user, 'Created administrator '.$user->email.' ('.Role::from($validated['role'])->label().')');

        return to_route('admin.users.index')->with('success', 'Account created. A link to set a password has been emailed to '.$user->email.'.');
    }

    public function edit(User $user): Response
    {
        return PageResponse::render('admin/users/Form', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->first()?->name,
                'is_active' => $user->is_active,
                'two_factor' => $user->hasConfirmedTwoFactor(),
            ],
            'roles' => $this->roles(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::enum(Role::class)],
            'is_active' => ['boolean'],
        ]);

        if ($user->is($request->user()) && ($validated['role'] !== Role::SuperAdmin->value || ! ($validated['is_active'] ?? true))) {
            return back()->with('error', 'You cannot remove your own Super Admin role or deactivate your own account.');
        }

        $user->update([
            'name' => $validated['name'],
            'email' => Str::lower($validated['email']),
            'is_active' => $validated['is_active'] ?? true,
        ]);
        $user->syncRoles([$validated['role']]);

        if (! $user->is_active) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        AuditLogger::log('user.updated', $user, 'Updated administrator '.$user->email);

        return back()->with('success', 'Account updated.');
    }

    public function resetTwoFactor(Request $request, User $user): RedirectResponse
    {
        $user->forceFill(['two_factor_secret' => null, 'two_factor_recovery_codes' => null, 'two_factor_confirmed_at' => null])->save();
        AuditLogger::log('user.2fa_reset', $user, 'Reset two-factor authentication for '.$user->email);

        return back()->with('success', 'Two-factor authentication reset. The user must enrol again at next sign-in.');
    }

    public function sendReset(User $user): RedirectResponse
    {
        Password::broker()->sendResetLink(['email' => $user->email]);
        AuditLogger::log('user.password_reset_sent', $user, 'Sent password reset link to '.$user->email);

        return back()->with('success', 'Password reset link sent.');
    }

    private function roles(): array
    {
        return array_map(fn (Role $role) => ['value' => $role->value, 'label' => $role->label()], Role::cases());
    }
}
