<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Register</title>
</head>
<body>
    <form action="{{ route('register') }}" method="post">
        @csrf
        <input type="text" name="name" placeholder="name"/>
        <br />
        <br />
        <input type="text" name="email" placeholder="email"/>
        <br />
        <br />
        <input type="password" name="password" placeholder="password" />
        <br>
        <br />
        <input type="password" name = "c_password" placeholder="confirmed paasword"/>
        <br>
        <br />
        <input type="submit" name="submit" value="submit" id="">
    </form>
</body>
</html>