<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Back-office staff accounts. Admin-only, and deliberately the *only* way an
 * account comes into existence — registration is disabled on the storefront,
 * and customers are not users at all.
 */
class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index', [
            'active' => 'users',
            'users' => User::orderBy('name')->get(),
        ]);
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create($data);

        return redirect()
            ->route('admin.users.index')
            ->with('status', $user->name.' can now sign in.');
    }


    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'role' => ['required', Rule::enum(UserRole::class)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        // Blank means "leave it alone" — the field is empty on every edit.
        if (blank($data['password'])) {
            unset($data['password']);
        }

        if ($this->wouldStrandTheStore($user, UserRole::from($data['role']))) {
            return back()->withInput()->withErrors([
                'role' => 'This is the last administrator — promote someone else first.',
            ]);
        }

        $user->update($data);

        return back()->with('status', $user->name.' saved.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        if ($this->wouldStrandTheStore($user, UserRole::Staff)) {
            return back()->withErrors(['user' => 'This is the last administrator — promote someone else first.']);
        }

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', $name.' removed.');
    }

    /**
     * Whether demoting or deleting this user would leave nobody able to manage
     * coupons, staff or the store — a door that locks behind you is a bug.
     */
    private function wouldStrandTheStore(User $user, UserRole $becoming): bool
    {
        if (! $user->isAdmin() || $becoming->managesStore()) {
            return false;
        }

        return User::where('role', UserRole::Admin)->whereKeyNot($user->id)->doesntExist();
    }
}
