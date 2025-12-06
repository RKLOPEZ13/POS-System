<?php
include('includes/connection.php');
session_start();

// Redirect if already logged in
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: pages/dashboard.php");
        exit();
    } elseif ($_SESSION['role'] === 'cashier') {
        header("Location: pages/POS.php");
        exit();
    }
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($first_name === '' || $password === '') {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE first_name = ?");
        $stmt->bind_param("s", $first_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($password === $user['password']) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header("Location: pages/dashboard.php");
                } else {
                    header("Location: pages/POS.php");
                }
                exit();
            } else {
                $error = "Invalid first name or password.";
            }
        } else {
            $error = "Invalid first name or password.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Twin Bites Snack Corner | Login</title>
<link rel="shortcut icon" href="image/lo.png" type="image/png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;900&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html, body {
  height: 100%;
  width: 100%;
  font-family: 'Nunito', sans-serif;
  overflow: hidden;
}

body {
  background: linear-gradient(135deg, #FFD95A 0%, #FF9B50 50%, #FF6B6B 100%);
  position: relative;
}

/* Animated background */
body::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 200%;
  height: 200%;
  transform: translate(-50%, -50%);
  background: repeating-conic-gradient(
    from 0deg,
    rgba(255, 255, 255, 0.1) 0deg 10deg,
    transparent 10deg 20deg
  );
  animation: rotate 40s linear infinite;
  pointer-events: none;
}

@keyframes rotate {
  from { transform: translate(-50%, -50%) rotate(0deg); }
  to { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Layout */
.login-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100vh;
  z-index: 1;
  position: relative;
  gap: 20px;
}

/* Logo */
.logo-container {
  width: 200px;
  height: 200px;
  background: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow:
    0 15px 40px rgba(0,0,0,0.2),
    0 0 0 12px rgba(255,255,255,0.3),
    0 0 0 24px rgba(255,255,255,0.1);
  animation: pulse 3s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.logo-container img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Titles */
.store-name {
  font-size: 60px;
  font-weight: 900;
  color: #2C5F8D;
  text-shadow:
    4px 4px 0 #FFF,
    8px 8px 0 #FF6B6B,
    4px 4px 20px rgba(0,0,0,0.3);
  text-transform: uppercase;
  letter-spacing: 2px;
  font-family: 'Poppins', sans-serif;
  animation: titleBounce 2s ease-in-out infinite;
  text-align: center;
}

@keyframes titleBounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}

.store-subtitle {
  font-size: 24px;
  color: #FFF;
  font-weight: 600;
  text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
  margin-bottom: 20px;
  letter-spacing: 6px;
  text-align: center;
}

/* Form styling */
form {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 18px;
}

input[type=text],
input[type=password] {
  width: 320px;
  padding: 14px 18px;
  border: none;
  border-radius: 50px;
  font-size: 16px;
  background: rgba(255,255,255,0.9);
  box-shadow: inset 0 0 0 2px rgba(255,255,255,0.4);
  transition: all 0.3s ease;
}

input:focus {
  outline: none;
  box-shadow: 0 0 0 4px #FF9B50;
  background: #fff;
}

/* Submit button */
input[type=submit] {
  width: 320px;
  padding: 16px;
  font-size: 18px;
  font-weight: 700;
  color: #fff;
  background: linear-gradient(135deg, #E63946 0%, #C1121F 100%);
  border: none;
  border-radius: 60px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 2px;
  box-shadow:
    0 12px 30px rgba(230,57,70,0.4),
    inset 0 -4px 0 rgba(0,0,0,0.2);
}

input[type=submit]:hover {
  background: linear-gradient(135deg, #FF4757 0%, #E63946 100%);
  transform: translateY(-4px);
  box-shadow:
    0 18px 40px rgba(230,57,70,0.6),
    inset 0 -4px 0 rgba(0,0,0,0.3);
}

.error {
  color: #E63946;
  font-weight: 600;
  margin-top: 10px;
}
</style>
</head>
<body>

<div class="login-container">
  <div class="logo-container">
    <img src="assets/images/twin.png" alt="Twin Bites Logo">
  </div>

  <h1 class="store-name">Twin Bites</h1>
  <p class="store-subtitle">SNACK CORNER</p>

  <?php if ($error) echo "<div class='error'>$error</div>"; ?>

  <form method="POST">
    <input type="text" name="username" placeholder="Username (First Name)" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="submit" value="Login">
  </form>
</div>

</body>
</html>