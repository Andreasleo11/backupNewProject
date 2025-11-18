<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Permission::query();

        // Check if there's a search query
        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query
                ->where('name', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%");
        }

        // Paginate the results
        $permissions = $query->paginate(10);

        return view('admin.permissions.index', compact('permissions'));
    }

    public function store(Request $request)
    {
        Permission::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'A new permission created');
    }

    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'string|max:255',
        ]);

        $permission->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()
            ->route('superadmin.permissions.index')
            ->with('success', 'Permission updated successfully');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()
            ->route('superadmin.permissions.index')
            ->with('success', 'Permission deleted successfully');
    }
}
