<?php
// FOR STORING REGISTRATION DATA
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $conn = new mysqli("localhost", "root", "", "library");

    if ($conn->connect_error) {
        die("Connection Failed: " . $conn->connect_error);
    }

    // Sanitize inputs
    $username = trim($_POST["username"]);
    if (!preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $username)) {
        die("Username must start with a letter and can contain letters, numbers, or underscore");
    }
    $phone = trim($_POST["phone"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    /* ================= SERVER-SIDE VALIDATION ================= */

    if ($username === "" || $phone === "" || $email === "" || $password === "") {
        die("All fields are required");
    }

    if (!preg_match('/^\d{10}$/', $phone)) {
        die("Invalid phone number");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email address");
    }

    if (
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password)
    ) {
        die("Password must contain uppercase, lowercase, number and be at least 8 characters");
    }

    /* ================= DUPLICATE CHECK ================= */

    $check = $conn->prepare("SELECT id FROM users WHERE email=? OR username=?");
    $check->bind_param("ss", $email, $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        die("Email or username already exists");
    }
    $check->close();

    /* ================= INSERT USER ================= */

    // Hash password
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO users (username, phone, email, password) VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("ssss", $username, $phone, $email, $hash);

    if ($stmt->execute()) {
        header("Location: login.php?msg=registered");
        exit();
    } else {
        die("Registration failed");
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: url("library.jpg") no-repeat center center/cover;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .register-box {
            background: white;
            width: 420px;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.3);
        }

        .register-box h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 26px;
            font-weight: 700;
        }

        label {
            display: block;
            margin-top: 12px;
            font-size: 14px;
            font-weight: bold;
        }

        input {
            width: 95%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #bbb;
        }

        .password-info {
            font-size: 12px;
            margin-top: 10px;
            color: #444;
        }

        .password-info ul {
            padding-left: 18px;
            margin: 0;
        }

        .btn {
            width: 100%;
            background: #c66;
            color: white;
            padding: 12px;
            border: none;
            margin-top: 25px;
            border-radius: 6px;
            cursor: pointer;
        }

        .login-link {
            text-align: center;
            margin-top: 15px;
            font-size: 13px;
        }

        .error {
            font-size: 12px;
            color: red;
            margin-top: 5px;
        }
    </style>
</head>

<body>

    <div class="register-box">
        <h2>Create Your Account</h2>

        <form id="registerForm" method="POST" action="register.php">

            <label>Username</label>
            <input type="text" id="username" name="username" required>
            <div id="userError" class="error"></div>

            <label>Phone</label>
            <input type="text" id="phone" name="phone" required>
            <div id="phoneError" class="error"></div>

            <label>Email</label>
            <input type="email" id="email" name="email" required>
            <div id="emailError" class="error"></div>

            <label>Password</label>
            <input type="password" id="password" name="password" required>
            <div id="passError" class="error"></div>

            <div class="password-info">
                <strong>Password Requirements:</strong>
                <ul>
                    <li>At least 8 characters</li>
                    <li>Uppercase + lowercase + number</li>
                </ul>
            </div>

            <button class="btn" type="submit">Register</button>

            <div class="login-link">
                Already have an account? <a href="login.php">Login</a>
            </div>

        </form>
    </div>

    <script>
        document.getElementById("registerForm").addEventListener("submit", function (event) {
            let username = document.getElementById("username").value.trim();
            let phone = document.getElementById("phone").value.trim();
            let email = document.getElementById("email").value.trim();
            let password = document.getElementById("password").value.trim();

            let valid = true;

            document.getElementById("userError").textContent = "";
            document.getElementById("phoneError").textContent = "";
            document.getElementById("emailError").textContent = "";
            document.getElementById("passError").textContent = "";

            if (!/^[A-Za-z][A-Za-z0-9_]*$/.test(username)) {
                document.getElementById("userError").textContent =
                    "Username must start with a letter (numbers allowed after)";
                valid = false;
            }

            if (!/^\d{10}$/.test(phone)) {
                document.getElementById("phoneError").textContent = "Enter valid 10-digit phone";
                valid = false;
            }

            if (!email.includes("@")) {
                document.getElementById("emailError").textContent = "Invalid email";
                valid = false;
            }

            if (password.length < 8) {
                document.getElementById("passError").textContent = "At least 8 characters";
                valid = false;
            } else if (!/[A-Z]/.test(password) || !/[a-z]/.test(password) || !/[0-9]/.test(password)) {
                document.getElementById("passError").textContent = "Must contain uppercase, lowercase, number";
                valid = false;
            }

            if (!valid) event.preventDefault();
        });
    </script>

</body>

</html>