<?php 
include 'db.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareClinic</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="style.css">
    
    <!-- Local Overrides specific to Landing Page only -->
    <style>
        .hero-section {
            padding: 80px 20px;
            display: flex;
            justify-content: center;
            text-align: center;
        }
        .hero-title { font-size: 2.8rem; font-weight: 700; margin-bottom: 20px; }
        .hero-description { font-size: 1.1rem; color: var(--text-light); margin-bottom: 40px; max-width: 700px; margin-left: auto; margin-right: auto; }
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; max-width: 1200px; margin: 0 auto; }
        .feature-card { background: white; padding: 30px; border-radius: 15px; text-align: center; box-shadow: var(--shadow-sm); transition: transform 0.3s ease; border: 1px solid #eee; }
        .feature-card:hover { transform: translateY(-10px); box-shadow: var(--shadow-lg); }
        .feature-icon { font-size: 2.5rem; color: var(--primary-color); margin-bottom: 20px; }
        section { padding: 80px 5%; }
        .section-title { text-align: center; font-size: 2.2rem; margin-bottom: 15px; }
        .section-subtitle { text-align: center; color: var(--primary-color); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 50px; display: block; }
        footer { background: #1a252f; color: white; padding: 60px 5% 20px; margin-top: auto; }
        .footer-content { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; max-width: 1200px; margin: 0 auto 40px; }
        .footer-col h3 { margin-bottom: 20px; }
        .footer-col a { color: #bdc3c7; }
        .footer-col a:hover { color: white; }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-brand">
            <i class="fas fa-heartbeat"></i>
            CareClinic
        </div>
        <div class="nav-links">
            <a href="#" class="nav-link">Home</a>
            <a href="#why-us" class="nav-link">Why Us</a>
            <a href="#about" class="nav-link">About</a>
            <a href="#contact" class="nav-link">Contact</a>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <?php 
                    $dashboard_url = 'user_dashboard.php'; 
                    if($_SESSION['role'] == 'admin') $dashboard_url = 'admin_dashboard.php';
                    elseif($_SESSION['role'] == 'doctor') $dashboard_url = 'doctor_dashboard.php';
                ?>
                <a href="<?= $dashboard_url ?>" class="nav-link" style="color: var(--primary-color);">Dashboard</a>
                <a href="logout.php" class="nav-btn">Logout</a>
            <?php else: ?>
                <a href="login.php" class="nav-link">Login</a>
                <a href="register.php" class="nav-btn">Get Started</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-section">
        <div class="card" style="max-width: 900px; width: 100%;">
            <h1 class="hero-title">Welcome to CareClinic</h1>
            <p class="hero-description">
                Experience the future of medical consultation. Get instant AI powered preliminary diagnoses and seamless appointments with top rated specialists.
            </p>
            
            <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
                <a href="diagnosis.php" class="btn btn-primary">
                    <i class="fas fa-robot"></i> Start AI Diagnosis
                </a>
                <a href="book_appointment.php" class="btn btn-success">
                    <i class="fas fa-calendar-check"></i> Book Doctor
                </a>
            </div>
            
            <div style="margin-top: 30px; background: #fff8e1; color: #706a30ff; padding: 15px; border-radius: 8px; font-size: 0.9rem; border-left: 4px solid #f8db84ff; text-align: left; display: inline-block; max-width: 600px;">
                <i class="fas fa-info-circle"></i> <strong>Note:</strong> This AI system provides preliminary insights and is not a replacement for professional medical advice.
            </div>
        </div>
    </header>

    <!-- Why Us Section -->
    <section id="why-us">
        <span class="section-subtitle">Why Choose Us</span>
        <h2 class="section-title">Healthcare Redefined</h2>
        
        <div class="features-grid">
            <div class="feature-card">
                <i class="fas fa-brain feature-icon"></i>
                <h3>Advanced AI Analysis</h3>
                <p>Our state of the art algorithms analyze your symptoms with high precision to guide your next steps.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-user-md feature-icon"></i>
                <h3>Verified Specialists</h3>
                <p>Connect with certified and experienced doctors across various specializations for professional treatment.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-clock feature-icon"></i>
                <h3>24/7 Availability</h3>
                <p>Medical concerns don't wait for office hours. Access our AI diagnostic tools anytime, anywhere.</p>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" style="background: var(--bg-light);">
        <div style="display:flex; align-items:center; gap:50px; max-width:1100px; margin:0 auto; flex-wrap:wrap;">
            <div style="flex:1; height:400px; background:#dfe6e9; border-radius:20px; display:flex; align-items:center; justify-content:center; color:#b2bec3; font-size:3rem;">
                <i class="fas fa-hospital-user" style="font-size: 8rem; color: #0056b3;"></i>
            </div>
            <div style="flex:1;">
                <span class="section-subtitle" style="text-align:left; margin-bottom:20px;">About Us</span>
                <h2 style="font-size:2rem; margin-bottom:20px;">Bridging Technology & Care</h2>
                <p style="color:var(--text-light); margin-bottom:20px;">CareClinic was founded with a simple mission: to make healthcare accessible, efficient, and intelligent. We believe that technology should empower patients, not confuse them.</p>
                <div style="display: flex; gap: 20px;">
                    <div><h3 style="color: var(--primary-color); font-size: 2rem;">10k+</h3><p>Users Helped</p></div>
                    <div><h3 style="color: var(--primary-color); font-size: 2rem;">50+</h3><p>Specialists</p></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact">
        <span class="section-subtitle">Get in Touch</span>
        <h2 class="section-title">Contact Our Support</h2>
        <div class="grid-2" style="max-width:1000px; margin:0 auto;">
            <div>
                <p style="margin-bottom: 30px; color: var(--text-light);">Have questions about our AI or need help booking? Our support team is here to assist you.</p>
                <div style="display:flex; gap:15px; margin-bottom:25px; align-items:center;">
                    <i class="fas fa-map-marker-alt" style="color:var(--primary-color); font-size:1.2rem;"></i>
                    <div><h4>Location</h4><p>123 Peradeniya Road, Kandy</p></div>
                </div>
                <div style="display:flex; gap:15px; margin-bottom:25px; align-items:center;">
                    <i class="fas fa-envelope" style="color:var(--primary-color); font-size:1.2rem;"></i>
                    <div><h4>Email</h4><p>support@careclinic.com</p></div>
                </div>
            </div>
            <form action="https://formspree.io/f/xlggrjgd" method="POST" id="Cform" novalidate>
                <div id="js-error-container"></div>
                <div class="form-group"><input name="name" id="name" type="text" class="form-control" placeholder="Your Name"></div>
                <div class="form-group"><input name="email" id="email" type="email" class="form-control" placeholder="Your Email"></div>
                <div class="form-group"><textarea name="message" id="message" class="form-control" rows="4" placeholder="Message"></textarea></div>
                <button class="btn btn-primary" style="width:100%;">Send Message</button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-col">
                <h3 style="display: flex; align-items: center; gap: 10px;"><i class="fas fa-heartbeat"></i> CareClinic</h3>
                <p style="color: #bdc3c7;">Empowering patients with AI driven insights and connecting them with world class medical professionals.</p>
            </div>
            <div class="footer-col">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="login.php">Login</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Legal</h3>
                <ul>
                    <li><a href="#">Privacy</a></li>
                    <li><a href="#">Terms</a></li>
                </ul>
            </div>
        </div>
        <div class="text-center" style="border-top:1px solid rgba(255,255,255,0.1); padding-top:20px; color:#7f8c8d;">
            &copy; 2026 CareClinic. All rights reserved.
        </div>
    </footer>
        <script>
        document.getElementById('Cform').addEventListener('submit', function(e) {
            e.preventDefault();
            
            let errors = [];
            const name = document.getElementById('name').value.trim();        
            const email = document.getElementById('email').value.trim();
            const message = document.getElementById('message').value.trim();
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

            if (message.length < 10) {
                errors.push("Message must be at least 10 characters.");
            }

            if (errors.length > 0) {
                let errorHtml = '<div class="alert alert-error" style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #f5c6cb;">';
                errorHtml += '<ul style="margin: 0; padding-left: 20px;">';
                errors.forEach(function(error) {
                    errorHtml += '<li>' + error + '</li>';
                });
                errorHtml += '</ul></div>';
                
                errorContainer.innerHTML = errorHtml;
            } else {
                this.submit();
            }
        });

        document.querySelectorAll('#Cform .form-control').forEach(input => {
            input.addEventListener('input', () => {
                document.getElementById('js-error-container').innerHTML = '';
            });
        });
    </script>
</body>
</html>