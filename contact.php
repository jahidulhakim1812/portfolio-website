<!-- contact.php - Contact Form with DB storage -->
<?php require_once 'config.php'; include 'header.php'; 
$message = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $msg = trim($_POST['message'] ?? '');
    if($name && $email && $msg) {
        $stmt = $pdo->prepare("INSERT INTO contacts (name, email, phone, message) VALUES (?, ?, ?, ?)");
        if($stmt->execute([$name, $email, $phone, $msg])) {
            $message = '<div class="alert alert-success">Thank you! We will get back soon.</div>';
        } else {
            $message = '<div class="alert alert-danger">Error, try again.</div>';
        }
    } else {
        $message = '<div class="alert alert-warning">All fields required except phone.</div>';
    }
}
?>
<section class="page-hero bg-light py-4">
    <div class="container text-center">
        <h1>Contact Us</h1>
        <p>Let's discuss your next AR innovation</p>
    </div>
</section>
<section class="contact-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mb-4">
                <h3>Get In Touch</h3>
                <p>We’re excited to collaborate on immersive experiences.</p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-map-marker-alt text-primary"></i> 123 Innovation Hub, Silicon Valley, CA</li>
                    <li><i class="fas fa-phone"></i> +1 (823) 456-5588</li>
                    <li><i class="fas fa-envelope"></i> contact@artechsolutions.com</li>
                </ul>
            </div>
            <div class="col-lg-6">
                <?php echo $message; ?>
                <form method="POST">
                    <div class="mb-3"><input type="text" name="name" class="form-control" placeholder="Your Name" required></div>
                    <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Email Address" required></div>
                    <div class="mb-3"><input type="text" name="phone" class="form-control" placeholder="Phone Number"></div>
                    <div class="mb-3"><textarea name="message" rows="5" class="form-control" placeholder="Your Message" required></textarea></div>
                    <button type="submit" class="btn btn-primary">Send Message <i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php include 'footer.php'; ?>