<!-- portfolio.php - Show all projects -->
<?php require_once 'config.php'; include 'header.php'; 
$portfolioItems = $pdo->query("SELECT * FROM portfolios ORDER BY id DESC")->fetchAll();
?>
<section class="page-hero bg-light py-5">
    <div class="container text-center">
        <h1>Our Portfolio</h1>
        <p>Explore our successful AR implementations across industries.</p>
    </div>
</section>
<section class="portfolio-grid py-5">
    <div class="container">
        <div class="row g-4">
            <?php foreach($portfolioItems as $item): ?>
            <div class="col-md-6 col-lg-4">
                <div class="project-card h-100">
                    <div class="project-img" style="background-image: url('<?php echo htmlspecialchars($item['image_url']); ?>'); height: 200px;"></div>
                    <div class="p-4">
                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                        <p><strong>Client:</strong> <?php echo htmlspecialchars($item['client']); ?></p>
                        <p><?php echo htmlspecialchars($item['description']); ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php include 'footer.php'; ?>