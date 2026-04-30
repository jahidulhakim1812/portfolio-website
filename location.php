<!-- location.php - Google Maps + Address -->
<?php require_once 'config.php'; include 'header.php'; ?>
<section class="page-hero bg-light py-4">
    <div class="container text-center">
        <h1>Our Location</h1>
        <p>Come visit our innovation lab</p>
    </div>
</section>
<section class="location-map py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6 mb-4">
                <h3>AR Tech Solutions HQ</h3>
                <address>
                    <strong>Main Office:</strong><br>
                    123 Main Street, Suite 400<br>
                    Anytown, CA 12345<br>
                    United States<br>
                    <i class="fas fa-phone"></i> +1 (987) 654-3210<br>
                    <i class="fas fa-envelope"></i> hello@artechsolutions.com
                </address>
            </div>
            <div class="col-md-6 mb-4">
                <div class="mapouter"><div class="gmap_canvas"><iframe width="100%" height="300" id="gmap_canvas" src="https://maps.google.com/maps?q=San%20Francisco%2C%20CA&t=&z=13&ie=UTF8&iwloc=&output=embed" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe></div></div>
            </div>
        </div>
    </div>
</section>
<?php include 'footer.php'; ?>