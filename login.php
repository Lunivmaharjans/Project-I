<?php
session_start();

// Database Connection
$conn = new mysqli("localhost", "root", "", "library");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$emailError = "";
$passwordError = "";
$loginError = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emailPhone = trim($_POST["emailPhone"]);
    $password   = trim($_POST["password"]);

    if (empty($emailPhone)) {
        $emailError = "Please enter email or phone.";
    }
    if (empty($password)) {
        $passwordError = "Please enter your password.";
    }

    if (empty($emailError) && empty($passwordError)) {

        // for librarian login
        if ($emailPhone === "admin" && $password === "123") {
            $_SESSION["username"] = "admin";
            $_SESSION["role"]     = "admin";
            header("Location: dashboard.html");
            exit();
        }

        // Copy the data from user 
        $stmt = $conn->prepare("SELECT * FROM users WHERE username=? OR phone=? OR email=? LIMIT 1");
        if (!$stmt) die("Query Error: " . $conn->error);
        $stmt->bind_param("sss", $emailPhone, $emailPhone, $emailPhone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION["user_id"]  = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"]     = $user["role"];
                header("Location: userdash.php");
                exit();
            } else {
                $loginError = "Incorrect password!";
            }
        } else {
            $loginError = "User not found!";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Page</title>
<style>
body {
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
    background: url("./assets/library bg.jpg") no-repeat center center/cover;
    height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.domain {
    margin-top: 40px;
    font-size: 32px;
    color: white;
    font-weight: 700;
    text-shadow: 0px 0px 5px rgba(0,0,0,0.7);
}

.login-box {
    background: white;
    width: 400px;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 0 10px rgba(0,0,0,0.3);
    margin-top: 80px;
    text-align: center;
}

.login-box h2 {
    margin-bottom: 20px;
    font-size: 22px;
    font-weight: 700;
}

label {
    display: block;
    text-align: left;
    font-size: 14px;
    font-weight: bold;
    margin-top: 20px;
    margin-bottom: 6px;
}

input {
    width: 100%;
    padding: 12px 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
    box-sizing: border-box;
}

.password-wrapper {
    position: relative;
    margin-bottom: 15px;
}

.forgot {
    font-size: 12px;
    text-align: right;
    margin-top: -10px;
    margin-bottom: 20px;
}

.btn {
    width: 100%;
    padding: 12px;
    margin-top: 10px;
    margin-bottom: 10px;
    background-color: #c66;
    color: white;
    border: none;
    font-size: 16px;
    border-radius: 6px;
    cursor: pointer;
}

.signup {
    margin-top: 15px;
    font-size: 13px;
    text-align: center;
}

.signup a {
    color: #007bff;
    text-decoration: none;
    font-weight: bold;
}

.error {
    color: red;
    font-size: 13px;
    margin-top: 5px;
    text-align: left;
}

.toggle-btn {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 12px;
    color: #007bff;
}

.loginErrorMsg {
    color: red;~
    font-size: 14px;
    margin-bottom: 10px;
    font-weight: bold;
}
</style>
</head>
<body>
<br><br>6
<div class="login-box">
    <h2>Welcome Back</h2>

    <?php if (!empty($loginError)) { ?>
        <div class="loginErrorMsg"><?php echo $loginError; ?></div>
    <?php } ?>

    <form method="POST" action="login.php">
        <label>Username</label>
        <input type="text" name="emailPhone" value="">
        <div class="error"><?php echo $emailError; ?></div>

        <label>Password</label>
        <div class="password-wrapper">
            <input type="password" name="password" id="password">
            <span class="toggle-btn" onclick="togglePassword()">Show</span>
        </div>
        <div class="error"><?php echo $passwordError; ?></div>

        <div class="forgot">
            <a href="forget.php">Forgot Password</a>
        </div>

        <button type="submit" class="btn">Login</button>

        <div class="signup">
            Don't Have an account? <a href="register.php">Sign Up</a>
        </div>
    </form>
</div>

<script>
// Show/Hide Password
function togglePassword() {
    const passField = document.getElementById("password");
    const toggleBtn = document.querySelector(".toggle-btn");

    if (passField.type === "password") {
        passField.type = "text";
        toggleBtn.textContent = "Hide";
    } else {
        passField.type = "password";
        toggleBtn.textContent = "Show";
    }
}
</script>

</body>
</html>
