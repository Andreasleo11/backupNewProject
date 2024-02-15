<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        $roles = Role::all();
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required',
            'role_id' => 'required',
            'department' => 'nullable'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->name . '1234'),
            'role_id' => $request->role_id,
            'department' => $request->department,
        ]);

        return redirect()->route('superadmin.users')->with(['success' => 'User added successfully!']);
    }

    public function update(Request $request, $id)
    {

        $user = User::find($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required',
            'role_id' => 'required',
            'department' => 'nullable'
        ]);

        $user->update($request->all());

        return redirect()->route('superadmin.users')->with(['success' => 'User updated successfully!']);

    }

    public function destroy($id)
    {
        User::find($id)->delete();
        return redirect()->back()->with(['success' => 'User deleted successfully!']);

    }
}
