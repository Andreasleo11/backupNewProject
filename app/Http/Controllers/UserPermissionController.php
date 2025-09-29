<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;

class UserPermissionController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('search')) {
            $query
                ->where('name', 'LIKE', '%'.$request->search.'%')
                ->orWhere('email', 'LIKE', '%'.$request->search.'%');
        }

        $users = $query->paginate(10); // Paginate with 10 users per page

        // for edit modal
        $permissionList = Permission::all();

        return view('admin.user-permissions.index', compact('users', 'permissionList'));
    }

    public function update(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'permissions' => 'array', // Ensure permissions is an array
            'permissions.*' => 'exists:permissions,id', // Ensure each permission exists in the permissions table
        ]);

        $user = User::find($id);

        $user->permissions()->sync($request->permissions);

        return redirect()->back()->with('success', 'Permissions updated successfully');
    }
}
