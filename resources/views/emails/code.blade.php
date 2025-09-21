<!doctype html>
<html>

<body>
    <h2>{{ $title ?? 'Code' }}</h2>
    <p>Your 6-digit code is:</p>
    <h1 style="letter-spacing:6px;">{{ $code }}</h1>
    <p>This code expires in 15 minutes.</p>
</body>

</html>
