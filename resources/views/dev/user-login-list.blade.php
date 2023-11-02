<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>User Login</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f7f7f7;
        }

        .button-container {
            text-align: center;
        }

        .post-button {
            margin-bottom: 10px;
            background-color: #4A90E2;
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            border-radius: 25px;
            cursor: pointer;
        }

        .post-button:hover {
            background-color: #357ABD; /* Darker shade of blue */
        }
    </style>
</head>
<body>
    <div class="button-container">
        @foreach ($users as $user)
        <form action="{{route('auth.login',['user_id' => $user->id])}}" method="POST">
            @csrf
            <input type="submit" class="post-button" value="Login as {{$user->name}}">
        </form>
    @endforeach
    </div>
    
    
</body>
</html>