<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 0;
            font-size: 16px;
            color: #ffffff;
            background-color: #3d4044;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        @yield('content')
    </div>
</body>

</html>
