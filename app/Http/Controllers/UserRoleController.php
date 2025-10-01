<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserRoleController extends Controller
{
    public function User()
    {
        $users = User::all(); // Get all users

        return response()->json($users);
    }

    public function assignRoleToME()
    {
        $roleId = 1;
        $user = \App\Models\User::find(1);
        $user->role_id = $roleId;
        $user->save();

        // Additional logic or redirection here
    }
}
