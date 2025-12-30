<?php
session_start();
$conn = new mysqli("localhost", "root", "", "library");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $emailPhone = trim($_POST["emailPhone"]);
    $newPassword = trim($_POST["newPassword"]);
    $confirmPassword = trim($_POST["confirmPassword"]);

    if (empty($emailPhone)) {
        $error = "Please enter your email or phone.";
    } elseif (empty($newPassword)) {
        $error = "Please enter a new password.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match!";
    } else {
        // Check if user exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username=? OR email=? OR phone=? LIMIT 1");
        $stmt->bind_param("sss", $emailPhone, $emailPhone, $emailPhone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Update/change password
            $update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $update->bind_param("si", $hashedPassword, $user['id']);
            if ($update->execute()) {
                $success = "Password reset successful! <a href='login.php'>Login Now</a>";
            } else {
                $error = "Failed to update password.";
            }
            $update->close();
        } else {
            $error = "User not found!";
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
<title>Forgot Password</title>
<style>
body {
    font-family: Arial;
    background: #f4f4f4;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
.container {
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(0,0,0,0.2);
    width: 350px;
}
h2{
     text-align: center; 
     margin-bottom: 20px; 
}

input{
     width: 100%; 
     padding: 10px; 
     margin: 8px 0; 
     border: 1px solid #ccc; 
     border-radius: 5px; 
    }

button {
    width: 100%; 
    padding: 10px; 
    background: #007bff; 
    color: #fff; 
    border: none; 
    border-radius: 5px; 
    cursor: pointer; 
}
button:hover { 
    background: #0056b3;
 }
.error { 
    color: red; 
    font-size: 14px; 
    margin-bottom: 10px; 
    text-align: center; 
}
.success {
     color: green; 
     font-size: 14px; 
     margin-bottom: 10px; 
     text-align: center; 
    }
.password-wrapper { 
    position: relative; 
}
.toggle-btn { 
    position: absolute; 
    right: 10px; 
    top: 50%; 
    transform: translateY(-50%); 
    cursor: pointer;
    color: #007bff; 
    font-size: 12px; 
    }
</style>
</head>
<body>

<div class="container">
    <h2>Reset Password</h2>

    <?php if($error) echo "<div class='error'>$error</div>"; ?>
    <?php if($success) echo "<div class='success'>$success</div>"; ?>

    <form method="POST" action="forget.php">
        <input type="text" name="emailPhone" placeholder="Phone" required>

        <div class="password-wrapper">
            <input type="password" name="newPassword" id="newPassword" placeholder="New Password" required>
            <span class="toggle-btn" onclick="togglePassword('newPassword', this)">Show</span>
        </div>

        <div class="password-wrapper">
            <input type="password" name="confirmPassword" id="confirmPassword" placeholder="Confirm Password" required>
            <span class="toggle-btn" onclick="togglePassword('confirmPassword', this)">Show</span>
        </div>

        <button type="submit">Reset Password</button>
    </form>
</div>

<script>
function togglePassword(fieldId, btn) {
    const field = document.getElementById(fieldId);
    if (field.type === "password") {
        field.type = "text";
        btn.textContent = "Hide";
    } else {
        field.type = "password";
        btn.textContent = "Show";
    }
}
</script>

</body>
</html>
