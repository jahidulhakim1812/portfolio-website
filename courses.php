<?php
// courses.php - Display all courses with search filter and details
require_once 'config.php';

// Fetch all active courses ordered by position
$courses = $pdo->query("SELECT * FROM courses WHERE status = 1 ORDER BY order_position ASC, id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>AR Tech Solutions | Our Courses</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;      /* Deep Blue */
            --primary-dark: #1d4ed8;
            --primary-light: #60a5fa;
            --secondary: #f97316;    /* Vibrant Orange */
            --secondary-dark: #ea580c;
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
        /* Search Box */
        .search-container {
            max-width: 500px;
            margin: 0 auto;
        }
        .search-input {
            border-radius: 60px;
            padding: 0.8rem 1.5rem;
            border: 1px solid var(--border-light);
            background: var(--bg-light);
            color: var(--text-light);
            width: 100%;
            font-size: 1rem;
            box-shadow: var(--shadow);
        }
        body.dark .search-input {
            background: var(--surface-dark);
            border-color: var(--border-dark);
            color: var(--text-dark);
        }
        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.2);
        }
        /* Course Cards */
        .course-card {
            background: var(--surface-light);
            border-radius: 28px;
            border: 1px solid var(--border-light);
            transition: all 0.3s;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        body.dark .course-card {
            background: var(--surface-dark);
            border-color: var(--border-dark);
        }
        .course-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 30px -12px rgba(0,0,0,0.2);
        }
        .course-img {
            width: 100%;
            height: 200px;
            object-fit: contain;
            background: var(--surface-light);
        }
        .course-icon-fallback {
            width: 100%;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            font-size: 3.5rem;
        }
        .course-body {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .course-title {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .course-meta {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        .level-badge {
            background: var(--primary);
            color: white;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .duration-badge {
            background: var(--secondary);
            color: white;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .price-badge {
            display: inline-block;
            background: #10b981;
            color: white;
            font-weight: 700;
            font-size: 1rem;
            padding: 4px 12px;
            border-radius: 30px;
            margin-bottom: 0.75rem;
        }
        .enrolled-info {
            font-size: 0.8rem;
            color: var(--text-muted-light);
            margin-top: 0.5rem;
        }
        body.dark .enrolled-info {
            color: var(--text-muted-dark);
        }
        .course-description {
            color: var(--text-muted-light);
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1.25rem;
            flex-grow: 1;
        }
        body.dark .course-description {
            color: var(--text-muted-dark);
        }
        .truncated-text {
            display: inline;
        }
        .full-text {
            display: none;
        }
        .read-more-btn {
            color: var(--primary);
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            margin-left: 5px;
            text-decoration: none;
        }
        .read-more-btn:hover {
            text-decoration: underline;
        }
        .btn-outline-custom {
            border: 2px solid var(--primary);
            background: transparent;
            border-radius: 40px;
            padding: 8px 20px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--primary);
            text-decoration: none;
            display: inline-block;
            transition: 0.2s;
            text-align: center;
            align-self: flex-start;
            margin-top: 0.5rem;
        }
        body.dark .btn-outline-custom {
            color: var(--secondary);
            border-color: var(--secondary);
        }
        .btn-outline-custom:hover {
            background: var(--primary);
            color: white;
        }
        body.dark .btn-outline-custom:hover {
            background: var(--secondary);
            color: var(--bg-dark);
        }
        .no-results {
            text-align: center;
            padding: 3rem;
        }
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
        /* Footer logo */
        .footer-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 1rem;
        }
        .footer-logo img {
            height: 40px;
            width: auto;
        }
        .footer-logo span {
            font-size: 1.4rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
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
            .course-col {
                flex: 0 0 50%;
                max-width: 50%;
            }
            .course-img, .course-icon-fallback {
                height: 170px;
            }
            .course-body {
                padding: 1rem;
            }
            .course-title {
                font-size: 1.1rem;
            }
            .navbar-brand img {
                height: 34px;
            }
            .footer-logo img {
                height: 30px;
            }
        }
        @media (max-width: 480px) {
            .course-img, .course-icon-fallback {
                height: 150px;
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

<!-- Navbar with Logo -->
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
                <li class="nav-item"><a class="nav-link" href="services.php">Services</a></li>
                <li class="nav-item"><a class="nav-link active" href="courses.php">Courses</a></li>
                <li class="nav-item"><a class="nav-link" href="portfolio.php">Portfolio</a></li>
                <li class="nav-item"><a class="nav-link" href="chairman-speech.php">Chairman</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
            </ul>
            <button id="darkModeToggle" class="dark-toggle ms-2"><i class="fas fa-moon"></i></button>
        </div>
    </div>
</nav>

<main>
    <!-- Hero Section -->
   

    <!-- Search Bar -->
    <section class="py-4">
        <div class="container">
            <div class="search-container">
                <input type="text" id="searchInput" class="search-input" placeholder="🔍 Search courses by title, level, or description...">
            </div>
        </div>
    </section>

    <!-- Courses Grid -->
    <section class="py-4">
        <div class="container">
            <div class="row g-4" id="coursesGrid">
                <?php if(count($courses) > 0): ?>
                    <?php foreach($courses as $course): ?>
                    <div class="col-6 col-md-6 col-lg-3 course-col" 
                         data-title="<?php echo strtolower(htmlspecialchars($course['title'])); ?>" 
                         data-level="<?php echo strtolower(htmlspecialchars($course['level'])); ?>"
                         data-desc="<?php echo strtolower(htmlspecialchars($course['description'])); ?>">
                        <a href="course-details.php?id=<?php echo $course['id']; ?>" class="text-decoration-none">
                            <div class="course-card">
                                <?php if(!empty($course['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($course['image_url']); ?>" class="course-img" alt="<?php echo htmlspecialchars($course['title']); ?>">
                                <?php else: ?>
                                    <div class="course-icon-fallback">
                                        <i class="<?php echo htmlspecialchars($course['icon_class']); ?> fa-3x"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="course-body">
                                    <h4 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h4>
                                    <div class="course-meta">
                                        <span class="level-badge"><?php echo htmlspecialchars($course['level']); ?></span>
                                        <span class="duration-badge"><?php echo htmlspecialchars($course['duration']); ?></span>
                                    </div>
                                    <?php if(!empty($course['price']) && $course['price'] > 0): ?>
                                        <div class="price-badge">$<?php echo number_format($course['price'], 2); ?></div>
                                    <?php endif; ?>
                                    <div class="course-description">
                                        <?php 
                                        $desc = htmlspecialchars($course['description']);
                                        $maxLen = 100;
                                        if(strlen($desc) > $maxLen): 
                                        ?>
                                            <span class="truncated-text"><?php echo substr($desc, 0, $maxLen); ?>...</span>
                                            <span class="full-text"><?php echo $desc; ?></span>
                                            <span class="read-more-btn" onclick="toggleReadMore(this)">Read more</span>
                                        <?php else: ?>
                                            <span><?php echo $desc; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="enrolled-info">
                                        <i class="fas fa-users"></i> <?php echo number_format($course['enrolled_students']); ?>+ students enrolled
                                    </div>
                                    <span class="btn-outline-custom">View Course →</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-graduation-cap fa-4x text-muted-custom mb-3"></i>
                        <h3>No courses available</h3>
                        <p>Check back soon for our upcoming courses.</p>
                    </div>
                <?php endif; ?>
            </div>
            <!-- No results message -->
            <div id="noResultsMsg" class="no-results" style="display: none;">
                <i class="fas fa-search fa-3x text-muted-custom mb-3"></i>
                <h4>No courses found</h4>
                <p class="text-muted-custom">Try adjusting your search term.</p>
            </div>
        </div>
    </section>
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
    // Toggle read more / read less
    function toggleReadMore(btn) {
        const wrapper = btn.parentNode;
        const truncated = wrapper.querySelector('.truncated-text');
        const full = wrapper.querySelector('.full-text');
        if (full.style.display === 'none' || getComputedStyle(full).display === 'none') {
            truncated.style.display = 'none';
            full.style.display = 'inline';
            btn.innerText = 'Read less';
        } else {
            truncated.style.display = 'inline';
            full.style.display = 'none';
            btn.innerText = 'Read more';
        }
    }

    // Search / Filter functionality
    function initSearch() {
        const searchInput = document.getElementById('searchInput');
        const courseCards = document.querySelectorAll('.course-col');
        const noResultsMsg = document.getElementById('noResultsMsg');
        const coursesGrid = document.getElementById('coursesGrid');

        if (!searchInput) return;

        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.trim().toLowerCase();
            let visibleCount = 0;

            courseCards.forEach(card => {
                const title = card.getAttribute('data-title') || '';
                const level = card.getAttribute('data-level') || '';
                const desc = card.getAttribute('data-desc') || '';
                const matches = title.includes(searchTerm) || level.includes(searchTerm) || desc.includes(searchTerm);

                if (searchTerm === '' || matches) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (visibleCount === 0) {
                noResultsMsg.style.display = 'block';
                coursesGrid.classList.add('justify-content-center');
            } else {
                noResultsMsg.style.display = 'none';
                coursesGrid.classList.remove('justify-content-center');
            }
        });
    }

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
            if (window.scrollY > 50) navbar.classList.add('scrolled');
            else navbar.classList.remove('scrolled');
        });
    }

    // Back to Top
    function initBackToTop() {
        const btn = document.getElementById('backToTop');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) btn.classList.add('show');
            else btn.classList.remove('show');
        });
        btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }

    // Floating Message
    document.getElementById('floatingMsg')?.addEventListener('click', () => {
        alert('Live chat support coming soon! 📱');
    });

    document.addEventListener('DOMContentLoaded', () => {
        initDarkMode();
        initNavbarScroll();
        initBackToTop();
        initSearch();
    });
</script>
</body>
</html>