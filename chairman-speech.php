<?php require_once 'config.php'; include 'header.php'; 
$speech = $pdo->query("SELECT * FROM chairman_speech WHERE is_active = 1 LIMIT 1")->fetch();
if(!$speech) die("Speech not found");
?>
<section class="page-hero bg-light py-5">
    <div class="container text-center">
        <h1>Chairman's Speech</h1>
        <p>Vision, Mission & the Road Ahead</p>
    </div>
</section>
<section class="speech-full py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="text-center mb-4">
                    <img src="<?php echo htmlspecialchars($speech['image_url']); ?>" class="rounded-circle img-fluid shadow" style="width: 150px; height: 150px; object-fit: cover;">
                    <h3 class="mt-3"><?php echo htmlspecialchars($speech['chairman_name']); ?></h3>
                    <p class="text-muted"><?php echo htmlspecialchars($speech['title']); ?></p>
                </div>
                <div class="speech-text lead" style="font-size: 1.2rem; line-height: 1.8;">
                    <?php echo nl2br(htmlspecialchars($speech['speech_text'])); ?>
                </div>
                <?php if($speech['signature_url']): ?>
                <div class="text-end mt-5">
                    <img src="<?php echo htmlspecialchars($speech['signature_url']); ?>" style="max-width: 200px;" alt="Signature">
                    <p class="mt-2"><?php echo htmlspecialchars($speech['chairman_name']); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php include 'footer.php'; ?>