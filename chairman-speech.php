<?php
// chairman-speech.php - Full page with chairman speech and team members with search
require_once 'config.php';

// Fetch active chairman speech
$speech = $pdo->query("SELECT * FROM chairman_speech WHERE is_active = 1 LIMIT 1")->fetch();
if (!$speech) {
    die("Chairman speech not found.");
}

// Fetch active team members ordered by position
$teamMembers = $pdo->query("SELECT * FROM team_members WHERE is_active = 1 ORDER BY order_position ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>AR Tech Solutions | Chairman's Speech</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ---------- SAME DESIGN SYSTEM AS OTHER PAGES ---------- */
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
        /* Navbar exactly as requested */
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
            letter-spacing: -0.5px;
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
        /* Chairman Speech Card */
        .speech-card {
            background: var(--surface-light);
            border-radius: 32px;
            padding: 2rem;
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow);
        }
        body.dark .speech-card {
            background: var(--surface-dark);
        }
        .speech-text {
            font-size: 1.2rem;
            line-height: 1.8;
            color: var(--text-light);
        }
        body.dark .speech-text {
            color: var(--text-dark);
        }
        /* Search Container */
        .search-container {
            max-width: 400px;
            margin: 0 auto 2rem auto;
            position: relative;
        }
        .search-input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.8rem;
            border-radius: 60px;
            border: 1px solid var(--border-light);
            background: var(--bg-light);
            color: var(--text-light);
            font-size: 0.95rem;
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
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted-light);
            font-size: 0.9rem;
        }
        body.dark .search-icon {
            color: var(--text-muted-dark);
        }
        .clear-search {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-muted-light);
            display: none;
        }
        .clear-search:hover {
            color: var(--primary);
        }
        /* Team Cards */
        .team-card {
            background: var(--surface-light);
            border-radius: 28px;
            border: 1px solid var(--border-light);
            transition: all 0.3s;
            overflow: hidden;
            height: 100%;
            text-align: center;
            padding: 1.8rem;
            box-shadow: var(--shadow);
        }
        body.dark .team-card {
            background: var(--surface-dark);
            border-color: var(--border-dark);
        }
        .team-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.2);
        }
        .team-img {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
            border: 3px solid var(--primary);
        }
        .social-icons a {
            color: var(--text-muted-light);
            margin: 0 0.5rem;
            font-size: 1.2rem;
            transition: 0.2s;
        }
        body.dark .social-icons a {
            color: var(--text-muted-dark);
        }
        .social-icons a:hover {
            color: var(--primary);
        }
        .no-results {
            text-align: center;
            padding: 2rem;
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
        /* Floating message & back to top */
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
            .team-col {
                flex: 0 0 50%;
                max-width: 50%;
            }
            .search-container {
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

<!-- Navbar -->
<nav class="navbar navbar-expand-lg glass-nav" id="mainNavbar">
    <div class="container">
        <a class="navbar-brand" href="index.php">ARTECH</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="portfolio.php">Portfolio</a></li>
                <li class="nav-item"><a class="nav-link active" href="chairman-speech.php">Chairman</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
            </ul>
            <button id="darkModeToggle" class="dark-toggle ms-2"><i class="fas fa-moon"></i></button>
        </div>
    </div>
</nav>

<main>
    <!-- Hero -->
    <section class="page-hero">
        <div class="container">
            <h1 class="display-4 fw-bold">Chairman's Speech</h1>
            <p class="lead text-muted-custom">Vision, Mission & the Road Ahead</p>
        </div>
    </section>

    <!-- Chairman Speech Content -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="speech-card">
                        <div class="text-center mb-4">
                            <img src="<?php echo htmlspecialchars($speech['image_url']); ?>" class="rounded-circle shadow-lg border border-3 border-primary" style="width: 150px; height: 150px; object-fit: cover;">
                            <h3 class="mt-3"><?php echo htmlspecialchars($speech['chairman_name'] ?? 'Chairman'); ?></h3>
                            <p class="text-muted-custom"><?php echo htmlspecialchars($speech['title'] ?? 'Chairman & Founder'); ?></p>
                        </div>
                        <div class="speech-text">
                            <?php echo nl2br(htmlspecialchars($speech['speech_text'])); ?>
                        </div>
                        <?php if(!empty($speech['signature_url'])): ?>
                        <div class="text-end mt-5">
                            <img src="<?php echo htmlspecialchars($speech['signature_url']); ?>" style="max-width: 200px;" alt="Signature">
                            <p class="mt-2 mb-0"><?php echo htmlspecialchars($speech['chairman_name'] ?? 'Chairman'); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Introduction Section with Search -->
    <?php if(count($teamMembers) > 0): ?>
    <section class="py-5" style="background: rgba(124,58,237,0.03);">
        <div class="container">
            <div class="text-center mb-4">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">Leadership & Team</span>
                <h2 class="display-5 fw-bold mt-2">Meet Our Team</h2>
                <p class="text-muted-custom">The brilliant minds behind our immersive innovations</p>
            </div>
            
            <!-- Search Input -->
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="teamSearchInput" class="search-input" placeholder="Search by name, position or bio...">
                <i class="fas fa-times-circle clear-search" id="clearTeamSearch"></i>
            </div>

            <!-- Team Grid -->
            <div class="row g-4" id="teamGrid">
                <?php foreach($teamMembers as $member): ?>
                <div class="col-6 col-md-4 col-lg-3 team-col" data-name="<?php echo strtolower(htmlspecialchars($member['name'])); ?>" data-position="<?php echo strtolower(htmlspecialchars($member['position'])); ?>" data-bio="<?php echo strtolower(htmlspecialchars($member['bio'])); ?>">
                    <div class="team-card">
                        <img src="<?php echo htmlspecialchars($member['image_url']); ?>" class="team-img" alt="<?php echo htmlspecialchars($member['name']); ?>">
                        <h4 class="fs-5 mb-1"><?php echo htmlspecialchars($member['name']); ?></h4>
                        <p class="text-muted-custom small"><?php echo htmlspecialchars($member['position']); ?></p>
                        <p class="small mt-2"><?php echo htmlspecialchars($member['bio']); ?></p>
                        <div class="social-icons mt-3">
                            <?php if(!empty($member['social_facebook'])): ?>
                            <a href="<?php echo htmlspecialchars($member['social_facebook']); ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                            <?php endif; ?>
                            <?php if(!empty($member['social_twitter'])): ?>
                            <a href="<?php echo htmlspecialchars($member['social_twitter']); ?>" target="_blank"><i class="fab fa-twitter"></i></a>
                            <?php endif; ?>
                            <?php if(!empty($member['social_linkedin'])): ?>
                            <a href="<?php echo htmlspecialchars($member['social_linkedin']); ?>" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div id="teamNoResults" class="no-results" style="display: none;">
                <i class="fas fa-user-friends fa-3x text-muted-custom mb-3"></i>
                <h4>No team members found</h4>
                <p>Try a different search term.</p>
            </div>
        </div>
    </section>
    <?php endif; ?>
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
    // Team Search Functionality
    const teamSearchInput = document.getElementById('teamSearchInput');
    const clearTeamSearch = document.getElementById('clearTeamSearch');
    const teamCards = document.querySelectorAll('.team-col');
    const teamGrid = document.getElementById('teamGrid');
    const teamNoResults = document.getElementById('teamNoResults');

    function filterTeam() {
        const query = teamSearchInput.value.toLowerCase().trim();
        let hasResults = false;

        teamCards.forEach(card => {
            const name = card.getAttribute('data-name') || '';
            const position = card.getAttribute('data-position') || '';
            const bio = card.getAttribute('data-bio') || '';
            if (name.includes(query) || position.includes(query) || bio.includes(query)) {
                card.style.display = '';
                hasResults = true;
            } else {
                card.style.display = 'none';
            }
        });

        if (hasResults) {
            teamNoResults.style.display = 'none';
        } else {
            teamNoResults.style.display = 'block';
        }

        clearTeamSearch.style.display = query.length > 0 ? 'block' : 'none';
    }

    if (teamSearchInput) {
        teamSearchInput.addEventListener('input', filterTeam);
        clearTeamSearch.addEventListener('click', () => {
            teamSearchInput.value = '';
            filterTeam();
            teamSearchInput.focus();
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