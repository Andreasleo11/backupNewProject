<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">
    <form action="{{ route('employee.login.submit') }}" class="bg-white p-6 rounded shadow w-96" method="post">
        @csrf
        <h1 class="text-xl font-bold mb-4">Employee Login</h1>
        @if ($errors->any())
            <p class="text-red-600 text-sm mb-4">{{ $errors->first() }}</p>
        @endif

        <label class="block mb-3">
            <span>NIK</span>
            <input type="text" name="nik" class="mt-1 w-full border rounded px-3 py-2">
        </label>
        <label class="block mb-3">
            <span>Password</span>
            <input type="password" name="password" class="mt-1 w-full border rounded px-3 py-2">
        </label>
        <!-- <label class="flex items-center mb-3">
            <input type="checkbox" name="remember" class="mr-2">
            <span>Remember me</span>
        </label> -->

        <button class="bg-blue-600 text-white w-full py-2 rounded hover:bg-blue-700">
            Login
        </button>
    </form>
</body>

</html>
