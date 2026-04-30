<!-- index.php - Homepage with fullscreen slider + services + projects + testimonials -->
<?php require_once 'config.php'; ?>
<?php include 'header.php'; ?>

<?php
// Fetch slider images from DB
$stmt = $pdo->query("SELECT * FROM sliders WHERE status = 1 ORDER BY order_position ASC");
$sliders = $stmt->fetchAll();

// Fetch services
$services = $pdo->query("SELECT * FROM services ORDER BY id LIMIT 4")->fetchAll();

// Fetch featured projects (portfolio featured items)
$featuredProjects = $pdo->query("SELECT * FROM portfolios WHERE featured = 1 ORDER BY id DESC LIMIT 3")->fetchAll();

// Fetch testimonials
$testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY id LIMIT 3")->fetchAll();
?>

<!-- Fullscreen Slider Section -->
<section class="fullscreen-slider">
    <div class="swiper heroSwiper">
        <div class="swiper-wrapper">
            <?php foreach($sliders as $slide): ?>
            <div class="swiper-slide" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?php echo htmlspecialchars($slide['image_url']); ?>');">
                <div class="slide-content container">
                    <h1 class="slide-title"><?php echo htmlspecialchars($slide['title']); ?></h1>
                    <p class="slide-subtitle"><?php echo htmlspecialchars($slide['subtitle']); ?></p>
                    <?php if($slide['button_text']): ?>
                    <a href="<?php echo htmlspecialchars($slide['button_link']); ?>" class="btn btn-primary btn-lg"><?php echo htmlspecialchars($slide['button_text']); ?></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-pagination"></div>
    </div>
</section>

<!-- Services Section -->
<section class="services-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Our Services</h2>
            <p class="section-subtitle">Cutting-edge AR solutions for modern enterprises</p>
        </div>
        <div class="row g-4">
            <?php foreach($services as $service): ?>
            <div class="col-md-6 col-lg-3">
                <div class="service-card text-center p-4 h-100">
                    <div class="service-icon">
                        <i class="<?php echo htmlspecialchars($service['icon_class']); ?>"></i>
                    </div>
                    <h4><?php echo htmlspecialchars($service['title']); ?></h4>
                    <p><?php echo htmlspecialchars($service['description']); ?></p>
                    <a href="<?php echo htmlspecialchars($service['link_url']); ?>" class="service-link">Learn More <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Projects Section -->
<section class="projects-section bg-light py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Featured Projects</h2>
            <p class="section-subtitle">Real-world impact with immersive technology</p>
        </div>
        <div class="row g-4">
            <?php foreach($featuredProjects as $project): ?>
            <div class="col-md-6 col-lg-4">
                <div class="project-card">
                    <div class="project-img" style="background-image: url('<?php echo htmlspecialchars($project['image_url']); ?>');"></div>
                    <div class="project-body p-3">
                        <h4><?php echo htmlspecialchars($project['title']); ?></h4>
                        <p class="client-name"><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($project['client']); ?></p>
                        <p><?php echo htmlspecialchars(substr($project['description'], 0, 100)) . '...'; ?></p>
                        <a href="portfolio.php" class="btn btn-outline-primary btn-sm">View Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="portfolio.php" class="btn btn-primary">View All Projects <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Client Testimonials</h2>
            <p class="section-subtitle">What our partners say about us</p>
        </div>
        <div class="row g-4">
            <?php foreach($testimonials as $testimonial): ?>
            <div class="col-md-4">
                <div class="testimonial-card p-4 h-100">
                    <div class="testimonial-icon">
                        <i class="fas fa-quote-left"></i>
                    </div>
                    <p class="testimonial-text">"<?php echo htmlspecialchars($testimonial['testimonial_text']); ?>"</p>
                    <div class="testimonial-author">
                        <h5><?php echo htmlspecialchars($testimonial['client_name']); ?></h5>
                        <span><?php echo htmlspecialchars($testimonial['client_title'] . ', ' . $testimonial['company']); ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Quick Contact Call to Action -->
<section class="cta-section py-5 text-center bg-primary text-white">
    <div class="container">
        <h3 class="mb-3">Ready to Transform Your Business with AR?</h3>
        <p class="mb-4">Let's discuss your next immersive project.</p>
        <a href="contact.php" class="btn btn-light btn-lg">Get in Touch <i class="fas fa-paper-plane"></i></a>
    </div>
</section>

<?php include 'footer.php'; ?>