<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livo/title>

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background: url('bg-library.jpg') no-repeat center center/cover;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(2px);
        }

        .container {
            width: 400px;
            background: rgba(255, 255, 255, 0.9);
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 0 30px rgba(0,0,0,0.4);
            text-align: center;
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .title {
            font-size: 22px;
            font-weight: 600;
        }

        .subtitle {
            font-size: 13px;
            margin-bottom: 20px;
            color: #555;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 15px;
            outline: none;
        }

        .btn {
            width: 100%;
            background: #2768ff;
            border: none;
            padding: 12px;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn:hover {
            background: #1f55d4;
        }

        .small-text {
            font-size: 13px;
            margin-top: 12px;
            color: #333;
        }

        a {
            color: #2768ff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="title">Welcome Back</div>
        <div class="subtitle">Sign in to access your digital library resources.</div>

        <form>
            <input type="text" placeholder="Username or Email" required>
            <input type="password" placeholder="Password" required>

            <button class="btn">Login</button>
        </form>

        <p class="small-text"><a href="#">Forgot Password?</a></p>
        <p class="small-text">New to LibraryHub? <a href="#">Register Now</a></p>
    </div>

</body>
</html>
