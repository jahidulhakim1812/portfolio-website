<?php
// contact.php - Contact page with AJAX form, contact info, and full-width map
require_once 'config.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $phone, $message]);

    $to = "hello@artechsolutions.com"; // CHANGE THIS TO YOUR EMAIL
    $subject = "New Contact Message from $name";
    $body = "Name: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message";
    $headers = "From: $email\r\nReply-To: $email";
    @mail($to, $subject, $body, $headers);

    echo json_encode(['success' => true, 'message' => 'Thank you! Your message has been sent. We will get back to you soon.']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Contact Us | AR Tech Solutions</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ========== SAME AS INDEX.PHP ========== */
        :root {
            --primary: #7c3aed;
            --primary-dark: #5b21b6;
            --primary-light: #a78bfa;
            --secondary: #06b6d4;
            --accent: #f43f5e;
            --bg-light: #ffffff;
            --bg-dark: #0f0f12;
            --surface-light: #f8fafc;
            --surface-dark: #1e1e2a;
            --text-light: #1e293b;
            --text-dark: #e2e8f0;
            --text-muted-light: #64748b;
            --text-muted-dark: #94a3b8;
            --border-light: #e2e8f0;
            --border-dark: #2d3a4e;
            --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-light);
            color: var(--text-light);
            transition: background 0.3s ease, color 0.2s ease;
            overflow-x: hidden;
        }
        body.dark {
            background: var(--bg-dark);
            color: var(--text-dark);
        }
        * {
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }
        h1, h2, h3, h4, .brand {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
        }
        /* Navbar */
        .glass-nav {
            position: relative;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1030;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(124, 58, 237, 0.2);
            box-shadow: var(--shadow);
            transition: all 0.3s;
            padding: 0.5rem 1rem;
        }
        body.dark .glass-nav {
            background: rgba(15, 15, 18, 0.85);
            border-color: rgba(124, 58, 237, 0.4);
        }
        .glass-nav.scrolled {
            padding: 0.3rem 1rem;
            background: rgba(255, 255, 255, 0.98);
        }
        body.dark .glass-nav.scrolled {
            background: rgba(10, 10, 15, 0.98);
        }
        /* Navbar brand with logo and text */
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 1.6rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            transition: opacity 0.2s;
        }
        .navbar-brand img {
            height: 44px;
            width: auto;
            max-width: 180px;
            display: inline-block;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.05));
        }
        body.dark .navbar-brand img {
            filter: brightness(0.9);
        }
        @media (max-width: 576px) {
            .navbar-brand img {
                height: 34px;
            }
            .navbar-brand {
                font-size: 1.3rem;
                gap: 0.4rem;
            }
        }
        .nav-link {
            font-weight: 600;
            color: var(--text-light) !important;
            margin: 0 0.5rem;
            position: relative;
        }
        body.dark .nav-link {
            color: var(--text-dark) !important;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: 0.3s;
        }
        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }
        .dark-toggle {
            background: rgba(124, 58, 237, 0.15);
            border: none;
            border-radius: 40px;
            width: 44px;
            height: 44px;
            color: var(--primary);
            transition: 0.2s;
            margin-left: 0.5rem;
        }
        body.dark .dark-toggle {
            background: rgba(124, 58, 237, 0.3);
            color: var(--secondary);
        }
        /* Page Hero */
        .page-hero {
            background: var(--surface-light);
            padding: 3rem 0 2rem;
            text-align: center;
            border-bottom: 1px solid var(--border-light);
        }
        body.dark .page-hero {
            background: var(--surface-dark);
        }
        /* Glass Card */
        .glass-card {
            background: var(--surface-light);
            border-radius: 28px;
            border: 1px solid var(--border-light);
            padding: 1.8rem;
            height: 100%;
            box-shadow: var(--shadow);
        }
        body.dark .glass-card {
            background: var(--surface-dark);
            border-color: var(--border-dark);
        }
        /* Form elements */
        .form-control, .form-select {
            background: var(--bg-light);
            border: 1px solid var(--border-light);
            border-radius: 16px;
            padding: 0.75rem 1rem;
            color: var(--text-light);
        }
        body.dark .form-control, body.dark .form-select {
            background: var(--surface-dark);
            border-color: var(--border-dark);
            color: var(--text-dark);
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
            outline: none;
        }
        .btn-primary-custom {
            background: linear-gradient(95deg, var(--primary), var(--secondary));
            border: none;
            padding: 12px 32px;
            border-radius: 40px;
            font-weight: 600;
            color: white;
            transition: 0.3s;
        }
        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(124, 58, 237, 0.3);
        }
        .btn-outline-custom {
            border: 2px solid var(--primary);
            background: transparent;
            border-radius: 40px;
            padding: 8px 24px;
            color: var(--primary);
            font-weight: 500;
        }
        body.dark .btn-outline-custom {
            color: var(--secondary);
            border-color: var(--secondary);
        }
        /* Contact info items */
        .contact-info-item {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        .contact-icon {
            width: 48px;
            height: 48px;
            background: rgba(124, 58, 237, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--primary);
        }
        /* Map container - full width */
        .map-container {
            border-radius: 28px;
            overflow: hidden;
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow);
            height: 400px;
        }
        .map-container iframe {
            width: 100%;
            height: 100%;
            border: 0;
        }
        /* Footer */
        footer {
            background: #0f172a;
            color: #cbd5e1;
            padding: 3rem 0 1.5rem;
            margin-top: 3rem;
        }
        body.dark footer {
            background: #020617;
        }
        footer a {
            color: #94a3b8;
            text-decoration: none;
        }
        footer a:hover {
            color: var(--secondary);
        }
        footer .text-muted {
            color: white !important;
        }
        /* Floating message icon */
        .floating-msg {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--primary);
            width: 56px;
            height: 56px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 99;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: 0.2s;
            color: white;
            font-size: 1.6rem;
        }
        .floating-msg:hover {
            transform: scale(1.1);
            background: var(--secondary);
        }
        /* Back to top */
        .back-to-top {
            position: fixed;
            bottom: 100px;
            right: 30px;
            background: var(--primary-dark);
            width: 44px;
            height: 44px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: 0.3s;
            z-index: 99;
            color: white;
        }
        .back-to-top.show { opacity: 1; }
        /* Responsive */
        @media (max-width: 768px) {
            .navbar-collapse {
                background: rgba(255,255,255,0.95);
                border-radius: 28px;
                padding: 1rem;
                margin-top: 1rem;
            }
            body.dark .navbar-collapse {
                background: rgba(20,20,30,0.95);
            }
            .map-container {
                height: 300px;
            }
        }
        .text-muted-custom {
            color: var(--text-muted-light);
        }
        body.dark .text-muted-custom {
            color: var(--text-muted-dark);
        }
    </style>
</head>
<body>

<!-- Navbar with Logo (path: uploads/logo.png) -->
<nav class="navbar navbar-expand-lg glass-nav" id="mainNavbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <img src="uploads/logo.png" alt="ARTECH Logo">
            ARTECH
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link " href="services.php">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
                
                <li class="nav-item"><a class="nav-link" href="portfolio.php">Portfolio</a></li>
                <li class="nav-item"><a class="nav-link" href="chairman-speech.php">Chairman</a></li>
                <li class="nav-item"><a class="nav-link active" href="contact.php">Contact</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
            </ul>
            <button id="darkModeToggle" class="dark-toggle ms-2"><i class="fas fa-moon"></i></button>
        </div>
    </div>
</nav>

<main>
    <!-- Page Hero -->
  

    <div class="container py-5">
        <div class="row g-5">
            <!-- Left Column: Contact Form -->
            <div class="col-lg-6">
                <div class="glass-card">
                    <h3 class="mb-4"><i class="fas fa-envelope me-2"></i> Send us a message</h3>
                    <div id="formAlert"></div>
                    <form id="contactForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Your Name *</label>
                                <input type="text" name="name" id="contactName" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" id="contactEmail" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" id="contactPhone" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Your Message *</label>
                                <textarea name="message" id="contactMessage" rows="5" class="form-control" required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary-custom" id="submitBtn">Send Message <i class="fas fa-paper-plane ms-2"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Column: Contact Info -->
            <div class="col-lg-6">
                <div class="glass-card">
                    <h3 class="mb-4"><i class="fas fa-address-card me-2"></i> Contact Info</h3>
                    <div class="contact-info-item">
                        <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div><strong>Visit Us</strong><br>123 AR Avenue, Tech Valley,<br>Silicon City, 123456</div>
                    </div>
                    <div class="contact-info-item">
                        <div class="contact-icon"><i class="fas fa-phone-alt"></i></div>
                        <div><strong>Call Us</strong><br>+1 (823) 456-5588<br>+1 (800) 123-4567</div>
                    </div>
                    <div class="contact-info-item">
                        <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                        <div><strong>Email</strong><br>hello@artechsolutions.com<br>support@artechsolutions.com</div>
                    </div>
                    <div class="contact-info-item">
                        <div class="contact-icon"><i class="fas fa-clock"></i></div>
                        <div><strong>Working Hours</strong><br>Mon-Fri: 9:00 AM - 6:00 PM<br>Sat: 10:00 AM - 2:00 PM</div>
                    </div>
                    <div class="social-links mt-3">
                        <a href="#" class="btn btn-outline-custom btn-sm me-2"><i class="fab fa-facebook-f"></i> Facebook</a>
                        <a href="#" class="btn btn-outline-custom btn-sm me-2"><i class="fab fa-twitter"></i> Twitter</a>
                        <a href="#" class="btn btn-outline-custom btn-sm"><i class="fab fa-linkedin-in"></i> LinkedIn</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Full Width Map Section (below both columns) -->
        <div class="mt-5">
            <div class="glass-card">
                <h3 class="mb-3"><i class="fas fa-map me-2"></i> Our Location</h3>
                <div class="map-container">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3650.0421!2d90.4125!3d23.8103!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755c5d1b8e5d5b1%3A0x7e2a8f5d6e3c2a9!2sDhaka!5e0!3m2!1sen!2sbd!4v1234567890" 
                        allowfullscreen="" loading="lazy">
                    </iframe>
                </div>
                <div class="mt-3 text-center">
                    <a href="https://maps.google.com" target="_blank" class="btn btn-outline-custom btn-sm">Open in Google Maps <i class="fas fa-external-link-alt ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>

<!-- Floating Message Icon -->
<div class="floating-msg" id="floatingMsg">
    <i class="fas fa-comment-dots"></i>
</div>

<!-- Back to Top -->
<div class="back-to-top" id="backToTop">
    <i class="fas fa-arrow-up"></i>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Dark Mode Toggle
    function initDarkMode() {
        const toggleBtn = document.getElementById('darkModeToggle');
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark');
            toggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
        } else {
            toggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
        }
        toggleBtn.addEventListener('click', () => {
            document.body.classList.toggle('dark');
            const isDark = document.body.classList.contains('dark');
            localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
            toggleBtn.innerHTML = isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
        });
    }

    // Navbar Scroll Effect
    function initNavbarScroll() {
        const navbar = document.querySelector('.glass-nav');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // Back to Top
    function initBackToTop() {
        const btn = document.getElementById('backToTop');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) btn.classList.add('show');
            else btn.classList.remove('show');
        });
        btn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Floating Message Alert
    document.getElementById('floatingMsg')?.addEventListener('click', () => {
        alert('Live chat support coming soon! 📱');
    });

    // AJAX Form Submission
    const contactForm = document.getElementById('contactForm');
    const formAlert = document.getElementById('formAlert');
    const submitBtn = document.getElementById('submitBtn');

    contactForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
        
        const formData = new FormData();
        formData.append('name', document.getElementById('contactName').value);
        formData.append('email', document.getElementById('contactEmail').value);
        formData.append('phone', document.getElementById('contactPhone').value);
        formData.append('message', document.getElementById('contactMessage').value);
        
        try {
            const response = await fetch('contact.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                formAlert.innerHTML = `<div class="alert alert-success alert-dismissible fade show">${data.message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
                contactForm.reset();
                formAlert.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                formAlert.innerHTML = `<div class="alert alert-danger alert-dismissible fade show">${data.message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
            }
        } catch (error) {
            formAlert.innerHTML = `<div class="alert alert-danger alert-dismissible fade show">Network error. Please try again.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Send Message <i class="fas fa-paper-plane ms-2"></i>';
            setTimeout(() => {
                const alert = formAlert.querySelector('.alert');
                if (alert) bootstrap.Alert.getOrCreateInstance(alert).close();
            }, 5000);
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        initDarkMode();
        initNavbarScroll();
        initBackToTop();
    });
</script>
</body>
</html>