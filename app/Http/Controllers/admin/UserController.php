<?php

namespace App\Http\Controllers\admin;

use App\DataTables\UsersDataTable;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Role;
use App\Models\Specification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(UsersDataTable $dataTable)
    {
        $users = User::all();
        $roles = Role::all();
        $departments = Department::all();
        $specifications = Specification::all();

        // return view('admin.users.index', compact('users', 'roles', 'departments', 'specifications'));
        return $dataTable->render(
            'admin.users.index',
            compact('users', 'roles', 'departments', 'specifications'),
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|unique:users,email',
            'role' => 'required|int',
            'department' => 'required|int',
            'specification' => 'nullable|int',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->name.'1234'),
            'role_id' => $request->role,
            'department_id' => $request->department,
            'specification_id' => $request->specification,
        ]);

        $role = Role::findOrFail($request->role);
        $user->roles()->sync([$role->id]);

        // Debugging role assignment
        // Log::info('Assigned roles to user', ['user_id' => $user->id, 'role_id' => $role->id]);

        $user->syncPermissions();

        return redirect()
            ->route('admin.users')
            ->with(['success' => 'User added successfully!']);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required',
            'role' => 'required|int',
            'department' => 'required|int',
            'specification' => 'required|int',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role,
            'department_id' => $request->department,
            'specification_id' => $request->specification,
        ]);

        $role = Role::findOrFail($request->role);
        $user->roles()->sync([$role->id]);

        // Debugging role assignment
        // Log::info('Assigned roles to user', ['user_id' => $user->id, 'role_id' => $role->id]);

        $user->syncPermissions();

        return redirect()
            ->route('admin.users')
            ->with(['success' => 'User updated successfully!']);
    }

    public function destroy($id)
    {
        User::find($id)->delete();

        return redirect()
            ->back()
            ->with(['success' => 'User deleted successfully!']);
    }

    public function resetPassword($id)
    {
        $user = User::find($id);
        $newPassword = Str::lower($user->name).'1234';
        $user->password = Hash::make($newPassword);
        $user->save();

        return redirect()->back()->with('success', 'Password reset successfully.');
    }

    /**
     * Delete selected users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSelected(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json(
                ['message' => 'No records selected for deletion (server).'],
                422,
            );
        }

        try {
            foreach ($ids as $id) {
                User::find($id)->delete();
            }

            return response()->json(
                ['message' => 'Selected records deleted successfully (server).'],
                200,
            );
        } catch (\Exception $e) {
            // Handle deletion error
            return response()->json(
                ['message' => 'Failed to delete selected records (server).'],
                500,
            );
        }
    }
}
