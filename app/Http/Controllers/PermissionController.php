<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%')
                ->orWhere('email', 'LIKE', '%' . $request->search . '%');
        }

        $users = $query->paginate(10); // Paginate with 10 users per page
        $permissions = Permission::all();

        return view('admin.permissions.index', compact('users', 'permissions'));
    }

    public function store(Request $request)
    {
        Permission::create(['name' => $request->name]);
        return redirect()->back()->with('success', 'A new permission created');
    }

    public function update(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'permissions' => 'array', // Ensure permissions is an array
            'permissions.*' => 'exists:permissions,id' // Ensure each permission exists in the permissions table
        ]);

        $user = User::find($id);

        $user->permissions()->sync($request->permissions);
        return redirect()->back()->with('success', 'Permissions updated successfully');
    }

    public function manage()
    {
        $permissions = Permission::all();
        return view('admin.permissions.manage', compact('permissions'));
    }

    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    public function updatePermission(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $permission->update([
            'name' => $request->name,
        ]);

        return redirect()->route('superadmin.permissions.manage')->with('success', 'Permission updated successfully');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return redirect()->route('superadmin.permissions.manage')->with('success', 'Permission deleted successfully');
    }
}
