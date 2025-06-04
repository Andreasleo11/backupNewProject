<!-- resources/views/employee_login.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Employee Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    <form method="POST" action="{{ route('employee.login') }}" class="bg-white p-6 rounded shadow-md w-96">
        @csrf
        <h2 class="text-xl font-bold mb-4">Employee Login</h2>

        @error('nik')
            <p class="text-red-500 text-sm">{{ $message }}</p>
        @enderror
        <input type="text" name="nik" placeholder="NIK" class="w-full mb-3 p-2 border rounded" required>

        @error('password')
            <p class="text-red-500 text-sm">{{ $message }}</p>
        @enderror
        <input type="password" name="password" placeholder="Password" class="w-full mb-3 p-2 border rounded" required>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded w-full">Login</button>
    </form>
</body>
</html>
