<?php
// FOR STORING REGISTRATION DATA
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $conn = new mysqli("localhost", "root", "", "library");

    if ($conn->connect_error) {
        die("Connection Failed: " . $conn->connect_error);
    }

    $username = $_POST["username"];
    $phone = $_POST["phone"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Hash password
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert data
    $stmt = $conn->prepare("INSERT INTO users (username, phone, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $phone, $email, $hash);

    if ($stmt->execute()) {
        header("Location: login.php?msg=registered");
        exit();
    } else {
        echo "<script>alert('Error: Email already exists or insertion failed');</script>";
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
        box-shadow: 0 0 12px rgba(0,0,0,0.3);
    }

    .register-box h2 {
        text-align: center;
        margin-bottom: 25px;
        font-size: 26px;
        font-weight: 700;
    }
    label { display: block; margin-top: 12px; font-size: 14px; font-weight: bold; }
    input {
        width: 95%;
        padding: 10px;
        margin-top: 5px;
        border-radius: 5px;
        border: 1px solid #bbb;
    }
    .password-info { font-size: 12px; margin-top: 10px; color: #444; }
    .password-info ul { padding-left: 18px; margin: 0; }
    .btn { width: 100%; background: #c66; color: white; padding: 12px; border: none; margin-top: 25px; border-radius: 6px; cursor: pointer; }
    .login-link { text-align: center; margin-top: 15px; font-size: 13px; }
    .error { font-size: 12px; color: red; margin-top: 5px; }
</style>
</head>

<body>

<div class="register-box">
    <h2>Create Your Account</h2>

    <form id="registerForm" method="POST" action="register.php">

        <label>Username</label>
        <input type="text" id="username" name="username">
        <div id="userError" class="error"></div>

        <label>Phone</label>
        <input type="text" id="phone" name="phone">
        <div id="phoneError" class="error"></div>

        <label>Email</label>
        <input type="email" id="email" name="email">
        <div id="emailError" class="error"></div>

        <label>Password</label>
        <input type="password" id="password" name="password">
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
document.getElementById("registerForm").addEventListener("submit", function(event) {
    let username = document.getElementById("username").value.trim();
    let phone = document.getElementById("phone").value.trim();
    let email = document.getElementById("email").value.trim();
    let password = document.getElementById("password").value.trim();

    let valid = true;

    // Clear old errors
    document.getElementById("userError").textContent = "";
    document.getElementById("phoneError").textContent = "";
    document.getElementById("emailError").textContent = "";
    document.getElementById("passError").textContent = "";

    // Username check
    if (username === "") {
        document.getElementById("userError").textContent = "Username required";
        valid = false;
    }

    // Phone check
    if (!/^\d{10}$/.test(phone)) {
        document.getElementById("phoneError").textContent = "Enter valid 10-digit phone";
        valid = false;
    }

    // Email check
    if (!email.includes("@")) {
        document.getElementById("emailError").textContent = "Invalid email";
        valid = false;
    }

    // Password check
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
