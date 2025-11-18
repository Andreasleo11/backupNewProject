<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeLoginController extends Controller
{
    public function showLoginForm()
    {
        if(Auth::guard('employee')->check()) {
            return redirect('/employees/home');
        }
        return view('employee.auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'nik' => 'required',
            'password' => 'required',
        ]);

        $employee = Employee::where('NIK', $data['nik'])->first();

        if (! $employee) {
            return back()->withErrors(['nik' => 'NIK not found!']);
        }

        $expectedPassword = $employee->NIK.$employee->date_birth->format('dmY');

        if ($data['password'] !== $expectedPassword) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        Auth::guard('employee')->login($employee);
        session()->put('logged_in_employee_nik', $employee->NIK);
        return redirect('/employees/home');
    }

    public function logout()
    {
        Auth::guard('employee')->logout();
        session()->forget('logged_in_employee_nik');

        return redirect(route('employee.login'));
    }
}
