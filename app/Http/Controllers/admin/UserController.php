<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Role;
use App\Models\Specification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        $roles = Role::all();
        $departments = Department::all();
        $specifications = Specification::all();
        return view('admin.users.index', compact('users', 'roles', 'departments', 'specifications'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|unique',
            'role' => 'required|int',
            'department' => 'required|int',
            'specification' => 'nullable|int',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->name . '1234'),
            'role_id' => $request->role,
            'department_id' => $request->department,
            'specification_id' => $request->specification,
        ]);

        return redirect()->route('superadmin.users')->with(['success' => 'User added successfully!']);
    }

    public function update(Request $request, $id)
    {

        $user = User::find($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required',
            'role' => 'required|int',
            'department' => 'required|int',
            'specification' => 'required|int'
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role,
            'department_id' => $request->department,
            'specification_id' => $request->specification,
        ]);

        return redirect()->route('superadmin.users')->with(['success' => 'User updated successfully!']);

    }

    public function destroy($id)
    {
        User::find($id)->delete();
        return redirect()->back()->with(['success' => 'User deleted successfully!']);

    }

    public function resetPassword($id)
    {
        $user = User::find($id);
        $newPassword = Str::lower($user->name) . '1234';
        $user->password = Hash::make($newPassword);
        $user->save();

        return redirect()->back()->with('success', 'Password reset successfully.');
    }
}
