<?php
// portfolio.php - Show all projects with search, navbar and footer identical to index.php
require_once 'config.php';

// Fetch all portfolio items
$portfolioItems = $pdo->query("SELECT * FROM portfolios WHERE status = 1 ORDER BY order_position ASC, id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>AR Tech Solutions | Our Portfolio</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ---------- SAME DESIGN SYSTEM AS INDEX.PHP ---------- */
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
        /* Navbar - IDENTICAL TO INDEX.PHP (with icon) */
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
        .navbar-brand {
            font-size: 1.6rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
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
        /* Search Bar */
        .search-container {
            max-width: 500px;
            margin: -1.5rem auto 2rem auto;
            position: relative;
            z-index: 10;
        }
        .search-input {
            width: 100%;
            padding: 0.9rem 1.2rem 0.9rem 3rem;
            border-radius: 60px;
            border: 1px solid var(--border-light);
            background: var(--bg-light);
            color: var(--text-light);
            font-size: 1rem;
            box-shadow: var(--shadow);
            transition: all 0.3s;
        }
        body.dark .search-input {
            background: var(--surface-dark);
            border-color: var(--border-dark);
            color: var(--text-dark);
        }
        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
        }
        .search-icon {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted-light);
        }
        body.dark .search-icon {
            color: var(--text-muted-dark);
        }
        .clear-search {
            position: absolute;
            right: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-muted-light);
            display: none;
        }
        .clear-search:hover {
            color: var(--primary);
        }
        /* Portfolio Cards */
        .portfolio-card {
            background: var(--surface-light);
            border-radius: 28px;
            border: 1px solid var(--border-light);
            transition: all 0.3s;
            overflow: hidden;
            height: 100%;
            box-shadow: var(--shadow);
        }
        body.dark .portfolio-card {
            background: var(--surface-dark);
            border-color: var(--border-dark);
        }
        .portfolio-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.2);
        }
        .portfolio-img {
            height: 220px;
            background-size: cover;
            background-position: center;
        }
        .no-results {
            text-align: center;
            padding: 3rem;
        }
        /* Footer - IDENTICAL TO INDEX.PHP */
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
        /* Floating message icon (same as index) */
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
        /* Responsive grid */
        @media (max-width: 768px) {
            .portfolio-col {
                flex: 0 0 50%;
                max-width: 50%;
            }
            .navbar-collapse {
                background: rgba(255,255,255,0.95);
                border-radius: 28px;
                padding: 1rem;
                margin-top: 1rem;
            }
            body.dark .navbar-collapse {
                background: rgba(20,20,30,0.95);
            }
            .search-container {
                margin: -1rem auto 1.5rem auto;
                width: 90%;
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

<!-- Navbar - IDENTICAL TO INDEX.PHP (with icon, same links) -->
<nav class="navbar navbar-expand-lg glass-nav" id="mainNavbar">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-vr-cardboard me-2"></i>ARTECH</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link active" href="portfolio.php">Portfolio</a></li>
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
    <section class="page-hero">
        <div class="container">
            <h1 class="display-4 fw-bold">Our Portfolio</h1>
        </div>
    </section>

    <!-- Search Bar -->
    <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="searchInput" class="search-input" placeholder="Search by project title or client...">
        <i class="fas fa-times-circle clear-search" id="clearSearch"></i>
    </div>

    <!-- Portfolio Grid -->
    <section class="py-2">
        <div class="container">
            <div class="row g-4" id="portfolioGrid">
                <?php if(count($portfolioItems) > 0): ?>
                    <?php foreach($portfolioItems as $item): ?>
                    <div class="col-6 col-md-6 col-lg-4 portfolio-col" data-title="<?php echo strtolower(htmlspecialchars($item['title'])); ?>" data-client="<?php echo strtolower(htmlspecialchars($item['client'])); ?>">
                        <div class="portfolio-card h-100">
                            <div class="portfolio-img" style="background-image: url('<?php echo htmlspecialchars($item['image_url']); ?>');"></div>
                            <div class="p-4">
                                <h3 class="fs-4"><?php echo htmlspecialchars($item['title']); ?></h3>
                                <p class="text-muted-custom"><strong><i class="fas fa-building"></i> Client:</strong> <?php echo htmlspecialchars($item['client']); ?></p>
                                <p class="text-muted-custom"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-folder-open fa-4x text-muted-custom mb-3"></i>
                        <h3>No portfolio items yet</h3>
                        <p>Check back soon for our latest projects.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div id="noResultsMessage" class="no-results" style="display: none;">
                <i class="fas fa-search fa-3x text-muted-custom mb-3"></i>
                <h3>No matching projects found</h3>
                <p>Try a different search term.</p>
            </div>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>

<!-- Floating Message Icon (same as index) -->
<div class="floating-msg" id="floatingMsg">
    <i class="fas fa-comment-dots"></i>
</div>

<!-- Back to Top -->
<div class="back-to-top" id="backToTop">
    <i class="fas fa-arrow-up"></i>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearSearch');
    const portfolioItems = document.querySelectorAll('.portfolio-col');
    const noResultsMsg = document.getElementById('noResultsMessage');

    function filterProjects() {
        const query = searchInput.value.toLowerCase().trim();
        let hasResults = false;

        portfolioItems.forEach(item => {
            const title = item.getAttribute('data-title') || '';
            const client = item.getAttribute('data-client') || '';
            if (title.includes(query) || client.includes(query)) {
                item.style.display = '';
                hasResults = true;
            } else {
                item.style.display = 'none';
            }
        });

        if (hasResults) {
            noResultsMsg.style.display = 'none';
        } else {
            noResultsMsg.style.display = 'block';
        }

        clearBtn.style.display = query.length > 0 ? 'block' : 'none';
    }

    searchInput.addEventListener('input', filterProjects);
    clearBtn.addEventListener('click', () => {
        searchInput.value = '';
        filterProjects();
        searchInput.focus();
    });

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

    // Floating Message
    document.getElementById('floatingMsg')?.addEventListener('click', () => {
        alert('Live chat support coming soon! 📱');
    });

    document.addEventListener('DOMContentLoaded', () => {
        initDarkMode();
        initNavbarScroll();
        initBackToTop();
    });
</script>
</body>
</html>