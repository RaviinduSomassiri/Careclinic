<?php
include 'db.php';

$error = "";

if (isset($_GET['success'])) {
    $error = "<div class='alert alert-success'>Registration successful! Please login.</div>";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'admin') {
            header("Location: admin_dashboard.php");
        } elseif ($user['role'] == 'doctor') {
            header("Location: doctor_dashboard.php");
        } else {
            header("Location: user_dashboard.php");
        }
        exit;
    } else {
        $error = "<div class='alert alert-error'>Invalid email or password.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CareClinic</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand"><i class="fas fa-heartbeat"></i> CareClinic</a>
        <div class="nav-links">
            <a href="index.php" class="nav-link">Home</a>
            <a href="register.php" class="nav-btn">Register</a>
        </div>
    </nav>

    <div class="container-center">
        <div class="card" style="width: 100%; max-width: 450px;">
            <h2 class="card-title text-center" style="justify-content: center; margin-bottom: 30px;">
                <i class="fas fa-sign-in-alt"></i> Welcome Back
            </h2>
            
            <?= $error ?>

            <div id="js-error-container"></div>

            <form method="POST" action="" id="loginForm" novalidate>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <div style="position: relative;">
                        <input type="email" name="email" id="email" class="form-control" placeholder="name@example.com" style="padding-left: 40px;" required>
                        <i class="fas fa-envelope" style="position: absolute; left: 15px; top: 15px; color: #aaa;"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div style="position: relative;">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" style="padding-left: 40px;" required>
                        <i class="fas fa-lock" style="position: absolute; left: 15px; top: 15px; color: #aaa;"></i>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-success" style="width: 100%; margin-top: 10px;">Login</button>
            </form>

            <p class="text-center mt-3" style="color: var(--text-light);">
                New to CareClinic? <a href="register.php" style="color: var(--primary-color); font-weight: 600;">Create Account</a>
            </p>
        </div>
    </div>
        <script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        let errors = [];
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const errorContainer = document.getElementById('js-error-container');

        errorContainer.innerHTML = '';

        if (email === '') {
            errors.push("Email Address is required.");
        }

        if (password === '') {
            errors.push("Password is required.");
        }

        if (errors.length > 0) {
            e.preventDefault();
            
            let errorHtml = '<div class="alert alert-error" style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #f5c6cb;">';
            errorHtml += '<ul style="margin: 0; padding-left: 20px;">';
            errors.forEach(function(error) {
                errorHtml += '<li>' + error + '</li>';
            });
            errorHtml += '</ul></div>';
            
            errorContainer.innerHTML = errorHtml;
        }
    });
</script>
</body>
</html>