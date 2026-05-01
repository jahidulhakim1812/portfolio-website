<!-- header.php - Navigation & Head -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>AR Tech Solutions - Immersive Reality Experiences</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- Custom Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Navbar - Absolute on top for fullscreen slider effect on homepage only, but works for all -->
<nav class="navbar navbar-expand-lg main-navbar">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-vr-cardboard"></i> AR Tech Solutions
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
           <ul class="navbar-nav ms-auto">
    <li class="nav-item"><a class="nav-link <?php echo isActive('index'); ?>" href="index.php">Home</a></li>
    <li class="nav-item"><a class="nav-link <?php echo isActive('portfolio'); ?>" href="portfolio.php">Portfolio</a></li>
    <li class="nav-item"><a class="nav-link <?php echo isActive('chairman-speech'); ?>" href="chairman-speech.php">Chairman's Speech</a></li>
    <li class="nav-item"><a class="nav-link <?php echo isActive('contact'); ?>" href="contact.php">Contact</a></li>
    <li class="nav-item"><a class="nav-link <?php echo isActive('about'); ?>" href="about.php">About</a></li>
    <li class="nav-item"><a class="nav-link <?php echo isActive('location'); ?>" href="location.php">Location</a></li>
</ul>
        </div>
    </div>
</nav>

<main>