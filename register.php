<?php
include 'db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($password)) {
        $message = "<div class='alert alert-error'>All fields are required.</div>";
    } elseif (strlen($password) < 6) {
        $message = "<div class='alert alert-error'>Password must be at least 6 characters.</div>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$name, $email, $hashed_password]);
            
            header("Location: login.php?success=1");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = "<div class='alert alert-error'>This email is already registered.</div>";
            } else {
                $message = "<div class='alert alert-error'>Registration failed: " . $e->getMessage() . "</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - CareClinic</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="navbar-brand"><i class="fas fa-heartbeat"></i> CareClinic</a>
        <div class="nav-links">
            <a href="index.php" class="nav-link">Home</a>
            <a href="login.php" class="nav-btn">Login</a>
        </div>
    </nav>

    <div class="container-center">
        <div class="card" style="width: 100%; max-width: 450px;">
            <h2 class="card-title text-center" style="justify-content: center; margin-bottom: 30px;">
                <i class="fas fa-user-plus"></i> Create Account
            </h2>
            
            <?= $message ?>

            <div id="js-error-container"></div>

            <form method="POST" action="" id="registerForm" novalidate>
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="John Doe" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="name@example.com" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••••••" required>
                    <small style="color: var(--text-light); font-size: 0.8rem;">Min. 6 characters</small>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Register</button>
            </form>
            
            <p class="text-center mt-3" style="color: var(--text-light);">
                Already have an account? <a href="login.php" style="color: var(--primary-color); font-weight: 600;">Login here</a>
            </p>
        </div>
    </div>
        <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            let errors = [];
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const errorContainer = document.getElementById('js-error-container');

            errorContainer.innerHTML = '';

            if (name === '') {
                errors.push("Full Name is required.");
            }

            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email === '') {
                errors.push("Email Address is required.");
            } else if (!emailPattern.test(email)) {
                errors.push("Please enter a valid email address.");
            }

            if (password === '') {
                errors.push("Password is required.");
            } else if (password.length < 6) {
                errors.push("Password must be at least 6 characters long.");
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