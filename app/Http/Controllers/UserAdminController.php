<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Rules\StrongPassword;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserAdminController extends Controller
{
    public function index(Request $request)
    {
        $q    = trim($request->get('q', ''));
        $role = $request->get('role');

        $query = User::query();
        if ($q) {
            $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%")
                   ->orWhere('phone', 'like', "%{$q}%");
            });
        }
        if ($role) $query->role($role);

        $users = $query->latest()->paginate(20)->withQueryString();
        $roles = Role::orderBy('name')->pluck('name');

        return view('principal.users.index', compact('users', 'roles', 'q', 'role'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->pluck('name');
        return view('principal.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:120',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20|unique:users,phone',
            'password' => ['required', 'string', new StrongPassword],
            'role'     => 'required|string|exists:roles,name',
        ]);

        $user = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'] ? $this->normalizePhone($data['phone']) : null,
            'password'   => Hash::make($data['password']),
            'role_label' => $data['role'],
            'active'     => true,
        ]);

        $user->assignRole($data['role']);

        return redirect()->route('principal.users.index')->with('success', "User {$user->name} created with role {$data['role']}.");
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->pluck('name');
        return view('principal.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:120',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'phone'    => 'nullable|string|max:20|unique:users,phone,' . $user->id,
            'role'     => 'required|string|exists:roles,name',
        ]);

        $user->update([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'] ? $this->normalizePhone($data['phone']) : null,
            'role_label' => $data['role'],
        ]);

        $user->syncRoles([$data['role']]);

        return redirect()->route('principal.users.index')->with('success', 'User updated.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $data = $request->validate([
            'password' => ['required', 'string', new StrongPassword],
        ]);

        $user->update(['password' => Hash::make($data['password'])]);
        AuditLog::log('user.password_reset', $user, [], [], 'Admin reset password');

        return back()->with('success', "Password reset for {$user->name}.");
    }

    public function toggle(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate yourself.');
        }
        $old = $user->active;
        $user->update(['active' => !$user->active]);
        AuditLog::log('user.status_changed', $user, ['active' => $old], ['active' => !$old]);
        return back()->with('success', "User " . ($user->active ? 'activated' : 'deactivated') . ".");
    }

    protected function normalizePhone(string $phone): string
    {
        $p = preg_replace('/\D/', '', $phone);
        if (str_starts_with($p, '0')) $p = '254' . substr($p, 1);
        if (strlen($p) === 9)         $p = '254' . $p;
        return $p;
    }
}
