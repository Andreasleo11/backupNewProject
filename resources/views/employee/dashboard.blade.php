<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Welcome, {{ Auth::guard('employee')->user()->name }}</h1>
    <p>This is the employee dashboard.</p>
    <form action="{{ route('employee.logout') }}" method="post">
        @csrf
        <button type="submit">Logout</button>
    </form> 
</body>
</html>