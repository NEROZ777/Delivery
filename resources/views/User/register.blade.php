<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Create account</title>
</head>
<body>
  <div>
    <form id="register_form" method="POST" action={{ url('/api/register') }}>
      <label for="name">First Name: </label>
      <input type="text" id="first_name" name="first_name"><br><br>
  
      <label for="name">Last Name: </label>
      <input type="text" id="last_name" name="last_name"><br><br>
  
      {{-- <label for="email">Email: </label>
      <input type="text" id="email" name="email"><br><br> --}}
  
      <label for="phone_number">Phone Number: </label>
      <input type="text" id="phone_number" name="phone_number"><br><br>
  
      <label for="password">Password: </label>
      <input type="password" id="password" name="password"><br><br>
  
      <label for="password_confirmation">Confirm Password: </label>
      <input type="password" id="password_confirmation" name="password_confirmation"><br><br>
  
      <button type="submit">Sign Up</button>
        
    </form>
  </div>
  <div>
    @if ($errors->any())
      <ul>
        @foreach ($errors as $error)
            <li>{{ $error }}</li>
        @endforeach
      </ul>
    @endif
  </div>
</body>
</html>