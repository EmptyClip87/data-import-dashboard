<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class UserController extends Controller
{
    /**
     * Show a list of all users
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        $users = User::with(['roles', 'permissions'])->get();
        return view('users.index', compact('users'));
    }

    /**
     * Show a form to create a new user
     *
     * @return Application|Factory|View
     */
    public function create()
    {
        $roles = Role::all();
        $permissions = Permission::all();
        return view('users.create', compact('roles', 'permissions')); // Show create form view
    }

    /**
     * Store a new user
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        // Create and save the user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        $user->assignRole($request->role);
        if (!empty($validated['permissions'])) {
            foreach ($validated['permissions'] as $permission) {
                $user->givePermissionTo($permission);
            }
        }

        return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    /**
     * Show a form to edit a user
     *
     * @param $id
     * @return Application|Factory|View
     */
    public function edit($id)
    {
        $user = User::with(['roles', 'permissions'])->findOrFail($id);
        $roles = Role::all();
        $permissions = Permission::all();
        return view('users.edit', compact('user', 'roles', 'permissions'));
    }

    /**
     * Update a user
     *
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'nullable|string|exists:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'] ? bcrypt($validated['password']) : $user->password,
        ]);

        if ($validated['role']) {
            $user->syncRoles([$validated['role']]);
        }

        if (isset($validated['permissions'])) {
            $user->syncPermissions($validated['permissions']);
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    /**
     * Delete a user
     *
     * @param $id
     * @return RedirectResponse
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully');
    }
}
