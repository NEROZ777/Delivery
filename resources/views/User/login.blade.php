<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login</title>
</head>
<body>
    <div>
        <form id="login_form" method="POST" action={{ url('/api/login') }}>
            <label name="phone_number">Phone Number: </label>
            <input type="text" id="phone_number" name="phone_number"><br><br>

            <label name="password">Password: </label>
            <input type="password" id="password" name="password"><br><br>

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>