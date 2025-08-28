<!DOCTYPE html>
<html>

<head>
  <title>{{ env('APP_NAME') }}</title>
</head>

<body>
  {!! nl2br(e($mailData['body'])) !!}
</body>

</html>
