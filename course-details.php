<?php
/**
 * course-details.php — Course detail page with 3D background & glass design
 * (matches portfolio-details.php style)
 */
require_once 'config.php';

$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND status = 1");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: courses.php');
    exit;
}

/* ---- Helper: fetch simple lists ---- */
function fetchList(PDO $pdo, string $table, string $col, int $courseId): array {
    $stmt = $pdo->prepare("SELECT `$col` AS v FROM `$table` WHERE course_id = ? ORDER BY order_position ASC, id ASC");
    $stmt->execute([$courseId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
}

$delivery = $pdo->prepare("SELECT label, note FROM course_delivery_modes WHERE course_id = ? ORDER BY order_position ASC");
$delivery->execute([$course_id]);
$delivery = $delivery->fetchAll();

$features      = fetchList($pdo, 'course_features', 'feature_text', $course_id);
$highlights    = fetchList($pdo, 'course_highlights', 'highlight_text', $course_id);
$careers       = fetchList($pdo, 'course_careers', 'career_title', $course_id);
$prerequisites = fetchList($pdo, 'course_prereq_items', 'prereq_text', $course_id);
$audience      = fetchList($pdo, 'course_audience', 'audience_text', $course_id);
$software      = fetchList($pdo, 'course_software_items', 'software_name', $course_id);

/* ---- Modules + nested data ---- */
$modStmt = $pdo->prepare("SELECT * FROM course_modules WHERE course_id = ? ORDER BY order_position ASC, id ASC");
$modStmt->execute([$course_id]);
$modules = $modStmt->fetchAll();

foreach ($modules as &$m) {
    $b = $pdo->prepare("SELECT type_label, class_count FROM course_module_breakdown WHERE module_id = ? ORDER BY order_position ASC");
    $b->execute([$m['id']]);
    $m['breakdown'] = $b->fetchAll();

    $h = $pdo->prepare("SELECT highlight_text FROM course_module_highlights WHERE module_id = ? ORDER BY order_position ASC");
    $h->execute([$m['id']]);
    $m['highlights'] = $h->fetchAll(PDO::FETCH_COLUMN, 0);

    $p = $pdo->prepare("SELECT project_name FROM course_module_projects WHERE module_id = ? ORDER BY order_position ASC");
    $p->execute([$m['id']]);
    $m['projects'] = $p->fetchAll(PDO::FETCH_COLUMN, 0);

    $c = $pdo->prepare("SELECT class_no, class_type, topic, resource_label FROM course_classes WHERE module_id = ? ORDER BY class_no ASC");
    $c->execute([$m['id']]);
    $m['classes'] = $c->fetchAll();
}
unset($m);

/* ---- Pricing ---- */
$priceStmt = $pdo->prepare("SELECT * FROM course_pricing WHERE course_id = ? ORDER BY order_position ASC");
$priceStmt->execute([$course_id]);
$pricing = $priceStmt->fetchAll();

/* ---- Gallery ---- */
$galStmt = $pdo->prepare("SELECT label, image_url FROM course_gallery WHERE course_id = ? ORDER BY order_position ASC");
$galStmt->execute([$course_id]);
$gallery = $galStmt->fetchAll();

/* ---- Testimonials (course-specific first, fallback to general) ---- */
$tStmt = $pdo->prepare("SELECT * FROM testimonials WHERE course_id = ? AND status = 1 ORDER BY order_position ASC, id DESC");
$tStmt->execute([$course_id]);
$testimonials = $tStmt->fetchAll();
if (!$testimonials) {
    $testimonials = $pdo->query("SELECT * FROM testimonials WHERE status = 1 ORDER BY order_position ASC, id DESC LIMIT 6")->fetchAll();
}

/* ---- FAQ ---- */
$faqStmt = $pdo->prepare("SELECT question, answer FROM course_faqs WHERE course_id = ? ORDER BY order_position ASC");
$faqStmt->execute([$course_id]);
$faq = $faqStmt->fetchAll();

/* ---- Live enrollment count ---- */
$totalEnrolled = (int)$course['enrolled_students'];
try {
    $e = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE course_id = ? AND payment_status = 'completed'");
    $e->execute([$course_id]);
    $totalEnrolled += (int)$e->fetchColumn();
} catch (PDOException $ex) {}

function stars(float $r): string {
    $full = floor($r);
    $out = '';
    for ($i = 0; $i < 5; $i++) $out .= $i < $full ? '&#9733;' : '&#9734;';
    return $out;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?php echo htmlspecialchars($course['title']); ?> | ARTECH</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,600;14..32,700;14..32,800&family=Space+Grotesk:wght@400;500;600;700&family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ===== GLOBAL RESET & DARK MODE (DEFAULT DARK) ===== */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #050b17;
            color: #ffffff;
            transition: background 0.3s ease, color 0.2s ease;
            overflow-x: hidden;
        }
        body.light {
            background: #f0f4fc;
            color: #1a1a2e;
        }
        /* Light mode overrides for all text elements */
        body.light h1, body.light h2, body.light h3, body.light h4, body.light h5,
        body.light p, body.light span, body.light li, body.light a:not(.nav-link):not(.btn),
        body.light .text-muted-custom, body.light .text-muted {
            color: #1a1a2e !important;
        }
        body.light .glass-card {
            background: rgba(255, 255, 255, 0.92) !important;
            border-color: rgba(123, 47, 255, 0.25) !important;
        }
        body.light .client-badge {
            background: linear-gradient(135deg, #7b2fff, #00d4ff);
            color: white !important;
        }
        body.light .btn-back {
            border-color: #7b2fff;
            color: #7b2fff;
        }
        body.light .btn-back:hover {
            background: #7b2fff;
            color: white;
        }
        body.light .stat-item {
            background: rgba(0,0,0,0.04);
        }
        body.light .delivery-item {
            background: rgba(0,0,0,0.04);
        }
        body.light .pill {
            background: rgba(0,0,0,0.06);
        }
        body.light .swatch-btn {
            border-color: rgba(123,47,255,0.3);
            color: #1a1a2e;
        }
        body.light .swatch-btn.active {
            background: rgba(123,47,255,0.1);
            border-color: #7b2fff;
        }
        body.light .module-panel {
            border-color: rgba(123,47,255,0.15);
            background: rgba(0,0,0,0.03);
        }
        body.light .module-meta div {
            border-bottom-color: rgba(0,0,0,0.08);
        }
        body.light .tag-list span {
            background: rgba(0,0,0,0.06);
        }
        body.light .classtable th {
            border-bottom-color: rgba(0,0,0,0.1);
        }
        body.light .classtable td {
            border-bottom-color: rgba(0,0,0,0.05);
        }
        body.light .price-card {
            border-color: rgba(123,47,255,0.2);
            background: rgba(0,0,0,0.02);
        }
        body.light .price-card.alt {
            border-color: #7b2fff;
            background: rgba(123,47,255,0.05);
        }
        body.light .gallery-tile {
            background-color: rgba(0,0,0,0.05);
        }
        body.light .t-card {
            border-color: rgba(123,47,255,0.15);
            background: rgba(0,0,0,0.02);
        }
        body.light .faq-item {
            border-bottom-color: rgba(0,0,0,0.06);
        }
        body.light .faq-item summary::after {
            color: #7b2fff;
        }
        body.light footer {
            background: rgba(240, 244, 252, 0.95);
            border-top-color: #7b2fff;
            color: #2a2a48;
        }
        body.light footer a:hover { color: #7b2fff; }

        /* ---- DARK MODE TEXT COLOR OVERRIDE (force white for secondary text) ---- */
        body:not(.light) .text-muted,
        body:not(.light) .stat-item .lbl,
        body:not(.light) .delivery-item .note,
        body:not(.light) .module-meta div span,
        body:not(.light) .classtable th,
        body:not(.light) .price-card .cadence,
        body:not(.light) .faq-item p,
        body:not(.light) .t-card .role,
        body:not(.light) .code,
        body:not(.light) .gallery-tile,
        body:not(.light) .text-muted-custom {
            color: #ffffff !important;
        }

        /* 3D Canvas */
        #three-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }
        .main-content {
            position: relative;
            z-index: 2;
        }

        /* ===== GLASS NAVBAR ===== */
        .glass-nav {
            position: relative;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1030;
            background: rgba(5, 11, 23, 0.7);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(0, 212, 255, 0.3);
            transition: all 0.3s;
            padding: 0.5rem 1rem;
        }
        body.light .glass-nav {
            background: rgba(240, 244, 252, 0.85);
            border-bottom: 1px solid rgba(123, 47, 255, 0.3);
        }
        .glass-nav.scrolled {
            padding: 0.3rem 1rem;
            background: rgba(5, 11, 23, 0.95);
        }
        body.light .glass-nav.scrolled {
            background: rgba(240, 244, 252, 0.98);
        }
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-family: 'Orbitron', monospace;
            font-size: 1.6rem;
            font-weight: 800;
            background: linear-gradient(135deg, #7b2fff, #00d4ff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .navbar-brand img {
            height: 44px;
            width: auto;
            filter: brightness(1.1);
        }
        body.light .navbar-brand img {
            filter: brightness(0.9);
        }
        @media (max-width: 576px) {
            .navbar-brand img { height: 34px; }
            .navbar-brand { font-size: 1.3rem; }
        }
        .nav-link {
            font-family: 'Orbitron', monospace;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(238, 245, 255, 0.8) !important;
            margin: 0 0.5rem;
            position: relative;
        }
        body.light .nav-link {
            color: rgba(26, 26, 46, 0.8) !important;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: #00d4ff;
            transition: 0.3s;
        }
        body.light .nav-link::after {
            background: #7b2fff;
        }
        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }
        .dark-toggle {
            background: rgba(0,212,255,0.15);
            border: 1px solid #00d4ff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            color: #00d4ff;
            transition: 0.2s;
            margin-left: 0.5rem;
        }
        body.light .dark-toggle {
            background: rgba(123, 47, 255, 0.1);
            border-color: #7b2fff;
            color: #7b2fff;
        }
        .dark-toggle:hover {
            background: rgba(0,212,255,0.3);
        }

        /* ===== GLASS CARD ===== */
        .glass-card {
            background: rgba(8, 16, 32, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 32px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            margin-bottom: 2rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .glass-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 28px 50px rgba(0, 0, 0, 0.4);
        }
        body.light .glass-card {
            background: rgba(255, 255, 255, 0.92);
            border-color: rgba(123, 47, 255, 0.25);
        }
        .client-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #7b2fff, #00d4ff);
            color: white;
            padding: 6px 18px;
            border-radius: 40px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .btn-primary-custom {
            background: linear-gradient(95deg, #7b2fff, #00d4ff);
            border: none;
            padding: 12px 32px;
            border-radius: 40px;
            font-weight: 600;
            color: white;
            transition: 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,212,255,0.3);
            color: white;
        }
        .btn-back {
            border: 2px solid #00d4ff;
            border-radius: 40px;
            padding: 10px 28px;
            font-weight: 600;
            color: #00d4ff;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
            background: transparent;
        }
        body.light .btn-back {
            border-color: #7b2fff;
            color: #7b2fff;
        }
        .btn-back:hover {
            background: rgba(0,212,255,0.2);
            color: white;
            transform: translateX(-3px);
        }

        /* ===== Course‑specific ===== */
        .course-image {
            width: 100%;
            max-height: 350px;
            object-fit: contain;
            border-radius: 24px;
            background: rgba(0,0,0,0.15);
        }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
        }
        .stat-item {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 16px;
            padding: 1rem;
            text-align: center;
        }
        .stat-item .num {
            font-family: 'Orbitron', monospace;
            font-size: 1.6rem;
            font-weight: 700;
            color: #00d4ff;
        }
        body.light .stat-item .num {
            color: #7b2fff;
        }
        .stat-item .lbl {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
        }
        .delivery-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin: 1rem 0;
        }
        .delivery-item {
            background: rgba(0,0,0,0.15);
            border-radius: 12px;
            padding: 0.8rem 1.2rem;
            flex: 1 1 180px;
        }
        .delivery-item .label {
            font-weight: 600;
        }
        .delivery-item .note {
            font-size: 0.9rem;
            color: #94a3b8;
        }
        .checklist {
            list-style: none;
            padding: 0;
            margin: 0.5rem 0;
        }
        .checklist li {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            margin-bottom: 0.5rem;
        }
        .checklist li::before {
            content: "▹";
            color: #00d4ff;
            font-weight: bold;
        }
        body.light .checklist li::before {
            color: #7b2fff;
        }
        .pill-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            margin: 0.5rem 0;
        }
        .pill {
            background: rgba(0,0,0,0.15);
            border-radius: 40px;
            padding: 0.4rem 1.2rem;
            font-size: 0.85rem;
        }
        .swatch-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            margin-bottom: 1.5rem;
        }
        .swatch-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0.5rem 1.2rem;
            border: 1px solid rgba(0,212,255,0.3);
            border-radius: 40px;
            background: transparent;
            color: #eef5ff;
            cursor: pointer;
            transition: 0.2s;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
        }
        .swatch-btn.active {
            background: rgba(0,212,255,0.15);
            border-color: #00d4ff;
        }
        .swatch-btn .dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            flex: 0 0 auto;
        }
        .module-panel {
            display: none;
            border: 1px solid rgba(0,212,255,0.15);
            border-radius: 20px;
            padding: 1.5rem;
            background: rgba(0,0,0,0.1);
        }
        .module-panel.active { display: block; }
        .module-head {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .module-head .code {
            font-family: 'Orbitron', monospace;
            font-size: 0.8rem;
            color: #94a3b8;
        }
        .module-head h3 {
            font-size: 1.4rem;
            margin: 0.3rem 0 0.5rem;
        }
        .module-meta {
            display: grid;
            gap: 0.4rem;
            font-size: 0.9rem;
        }
        .module-meta div {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dashed rgba(255,255,255,0.1);
            padding-bottom: 0.3rem;
        }
        .module-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        .module-body h4 {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        .tag-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
        }
        .tag-list span {
            background: rgba(0,0,0,0.15);
            border-radius: 20px;
            padding: 0.2rem 0.8rem;
            font-size: 0.8rem;
        }
        .classtable {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        .classtable th {
            text-align: left;
            font-family: 'Orbitron', monospace;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
            padding: 0.4rem 0.2rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .classtable td {
            padding: 0.4rem 0.2rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .classtable tr:last-child td { border-bottom: none; }
        .type-tag {
            font-family: 'Orbitron', monospace;
            font-size: 0.7rem;
            padding: 0.1rem 0.6rem;
            background: rgba(0,0,0,0.1);
            border-radius: 12px;
        }
        .price-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
        }
        .price-card {
            border: 1px solid rgba(0,212,255,0.2);
            border-radius: 20px;
            padding: 1.5rem;
            background: rgba(0,0,0,0.1);
            transition: 0.2s;
        }
        .price-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        .price-card.alt {
            border-color: #7b2fff;
            background: rgba(123,47,255,0.05);
        }
        .price-card .amount {
            font-family: 'Orbitron', monospace;
            font-size: 2rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }
        .price-card .cadence {
            color: #94a3b8;
            font-size: 0.9rem;
        }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 0.5rem;
        }
        .gallery-tile {
            aspect-ratio: 4/5;
            background: rgba(0,0,0,0.15);
            border-radius: 16px;
            display: flex;
            align-items: flex-end;
            padding: 0.8rem;
            font-size: 0.8rem;
            color: #94a3b8;
            background-size: cover;
            background-position: center;
            background-color: rgba(0,0,0,0.1);
            transition: 0.2s;
        }
        .gallery-tile:hover {
            transform: scale(1.02);
        }
        .testimonial-rail {
            display: flex;
            gap: 1.5rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
            scroll-snap-type: x mandatory;
        }
        .t-card {
            flex: 0 0 280px;
            scroll-snap-align: start;
            border: 1px solid rgba(0,212,255,0.15);
            border-radius: 20px;
            padding: 1.5rem;
            background: rgba(0,0,0,0.1);
            transition: 0.2s;
        }
        .t-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .t-card .stars {
            color: #FFC53D;
            font-size: 0.9rem;
        }
        .t-card p {
            margin: 0.8rem 0;
            font-size: 0.95rem;
        }
        .t-card .who {
            font-weight: 600;
        }
        .t-card .role {
            font-size: 0.8rem;
            color: #94a3b8;
        }
        .faq-item {
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .faq-item summary {
            padding: 1rem 0;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
        }
        .faq-item summary::-webkit-details-marker { display: none; }
        .faq-item summary::after {
            content: "+";
            font-size: 1.2rem;
            color: #00d4ff;
        }
        .faq-item[open] summary::after {
            content: "–";
        }
        .faq-item p {
            padding: 0 0 1rem 0;
            color: #94a3b8;
            margin: 0;
        }
        .rail-controls {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .rail-controls button {
            width: 36px;
            height: 36px;
            border: 1px solid #00d4ff;
            border-radius: 50%;
            background: transparent;
            color: #00d4ff;
            cursor: pointer;
            transition: 0.2s;
        }
        .rail-controls button:hover {
            background: rgba(0,212,255,0.2);
        }

        /* ===== FOOTER ===== */
        footer {
            background: rgba(3, 6, 18, 0.9);
            border-top: 1px solid #00d4ff;
            color: #cbd5e1;
            padding: 3rem 0 1.5rem;
            margin-top: 3rem;
        }
        footer a {
            color: #94a3b8;
            text-decoration: none;
        }
        footer a:hover { color: #00d4ff; }

        /* ===== FLOATING ELEMENTS ===== */
        .floating-msg {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: rgba(0,212,255,0.2);
            backdrop-filter: blur(8px);
            border: 1px solid #00d4ff;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 99;
            transition: 0.2s;
            color: #00d4ff;
            font-size: 1.4rem;
        }
        .floating-msg:hover {
            transform: scale(1.1);
            background: rgba(0,212,255,0.4);
        }
        .back-to-top {
            position: fixed;
            bottom: 100px;
            right: 30px;
            background: rgba(0,212,255,0.25);
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: 0.3s;
            z-index: 99;
            color: #00d4ff;
        }
        .back-to-top.show { opacity: 1; }

        @media (max-width: 768px) {
            .module-head, .module-body {
                grid-template-columns: 1fr;
            }
            .glass-card {
                padding: 1.2rem;
            }
            .stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>

<!-- 3D Canvas Background -->
<canvas id="three-canvas"></canvas>

<div class="main-content">
    <!-- Navbar -->
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
        <div class="container py-5">

            <!-- ===== MAIN COURSE CARD ===== -->
            <div class="glass-card">
                <div class="row g-4">
                    <div class="col-md-5">
                        <?php if (!empty($course['image_url'])): ?>
                            <img src="<?= htmlspecialchars($course['image_url']) ?>" class="course-image" alt="<?= htmlspecialchars($course['title']) ?>">
                        <?php else: ?>
                            <div class="course-image d-flex align-items-center justify-content-center" style="background:linear-gradient(135deg,#7b2fff,#00d4ff);min-height:250px;border-radius:24px;">
                                <i class="fas fa-graduation-cap fa-5x text-white opacity-50"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-7">
                        <?php if (!empty($course['kicker'])): ?>
                            <div class="client-badge"><?= htmlspecialchars($course['kicker']) ?></div>
                        <?php endif; ?>
                        <h1 class="display-5 fw-bold"><?= htmlspecialchars($course['title']) ?></h1>
                        <p class="text-muted" style="font-size:1.05rem;"><?= nl2br(htmlspecialchars($course['short_description'] ?: $course['description'])) ?></p>

                        <div class="stat-grid">
                            <div class="stat-item">
                                <div class="num"><?= number_format($totalEnrolled) ?>+</div>
                                <div class="lbl">Enrolled</div>
                            </div>
                            <div class="stat-item">
                                <div class="num" style="color:#FFC53D;"><?= stars((float)$course['rating']) ?></div>
                                <div class="lbl"><?= number_format((float)$course['rating'], 1) ?> (<?= (int)$course['review_count'] ?> reviews)</div>
                            </div>
                            <div class="stat-item">
                                <div class="num"><?= htmlspecialchars($course['duration']) ?></div>
                                <div class="lbl">Duration</div>
                            </div>
                            <div class="stat-item">
                                <div class="num"><?= (int)$course['total_classes'] ?></div>
                                <div class="lbl">Classes</div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-3 mt-3">
                            <a href="enroll.php?course_id=<?= (int)$course['id'] ?>" class="btn-primary-custom">
                                <i class="fas fa-user-graduate"></i> Enroll Now
                            </a>
                            <a href="#modules" class="btn-back">
                                <i class="fas fa-list-ul"></i> View Outline
                            </a>
                        </div>
                    </div>
                </div>

                <?php if ($delivery): ?>
                    <div class="delivery-row mt-4 pt-3 border-top" style="border-color:rgba(255,255,255,0.05);">
                        <?php foreach ($delivery as $d): ?>
                            <div class="delivery-item">
                                <div class="label"><?= htmlspecialchars($d['label']) ?></div>
                                <div class="note"><?= htmlspecialchars($d['note']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ===== FEATURES & HIGHLIGHTS ===== -->
            <?php if ($features || $highlights): ?>
                <div class="row g-4">
                    <?php if ($features): ?>
                        <div class="col-md-6">
                            <div class="glass-card h-100">
                                <h3><i class="fas fa-check-circle me-2" style="color:#00d4ff;"></i> What's Included</h3>
                                <ul class="checklist">
                                    <?php foreach ($features as $f): ?><li><?= htmlspecialchars($f) ?></li><?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($highlights): ?>
                        <div class="col-md-6">
                            <div class="glass-card h-100">
                                <h3><i class="fas fa-star me-2" style="color:#FFC53D;"></i> Key Highlights</h3>
                                <ul class="checklist">
                                    <?php foreach ($highlights as $h): ?><li><?= htmlspecialchars($h) ?></li><?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- ===== CAREERS, PREREQUISITES, AUDIENCE, SOFTWARE ===== -->
            <?php if ($careers || $prerequisites || $audience || $software): ?>
                <div class="glass-card">
                    <?php if ($careers): ?>
                        <h3><i class="fas fa-briefcase me-2" style="color:#00d4ff;"></i> Career Outcomes</h3>
                        <div class="pill-grid">
                            <?php foreach ($careers as $c): ?><span class="pill"><?= htmlspecialchars($c) ?></span><?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($prerequisites || $audience): ?>
                        <div class="row g-4 mt-3">
                            <?php if ($prerequisites): ?>
                                <div class="col-md-6">
                                    <h4><i class="fas fa-list-check me-2" style="color:#FFC53D;"></i> Prerequisites</h4>
                                    <ul class="checklist">
                                        <?php foreach ($prerequisites as $p): ?><li><?= htmlspecialchars($p) ?></li><?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <?php if ($audience): ?>
                                <div class="col-md-6">
                                    <h4><i class="fas fa-users me-2" style="color:#FFC53D;"></i> Designed For</h4>
                                    <div class="pill-grid">
                                        <?php foreach ($audience as $a): ?><span class="pill"><?= htmlspecialchars($a) ?></span><?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($software): ?>
                        <div class="mt-3">
                            <h4><i class="fas fa-laptop-code me-2" style="color:#00d4ff;"></i> Software You'll Learn</h4>
                            <div class="pill-grid">
                                <?php foreach ($software as $s): ?><span class="pill"><?= htmlspecialchars($s) ?></span><?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- ===== MODULES ===== -->
            <?php if ($modules): ?>
                <div class="glass-card" id="modules">
                    <h3><i class="fas fa-layer-group me-2" style="color:#00d4ff;"></i> Course Outline</h3>
                    <p class="text-muted">Click a module below to see its full breakdown.</p>

                    <div class="swatch-nav">
                        <?php foreach ($modules as $i => $m): ?>
                            <button class="swatch-btn<?= $i === 0 ? ' active' : '' ?>" data-target="mod-<?= htmlspecialchars($m['module_code']) ?>">
                                <span class="dot" style="background:<?= htmlspecialchars($m['swatch_color']) ?>;"></span>
                                <?= htmlspecialchars($m['module_code']) ?> — <?= htmlspecialchars($m['title']) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <?php foreach ($modules as $i => $m): ?>
                        <div class="module-panel<?= $i === 0 ? ' active' : '' ?>" id="mod-<?= htmlspecialchars($m['module_code']) ?>">
                            <div class="module-head">
                                <div>
                                    <div class="code">MODULE <?= htmlspecialchars($m['module_code']) ?></div>
                                    <h3><?= htmlspecialchars($m['title']) ?></h3>
                                    <?php if ($m['focus_text']): ?><p class="text-muted"><?= htmlspecialchars($m['focus_text']) ?></p><?php endif; ?>
                                </div>
                                <div class="module-meta">
                                    <div><span>Duration</span><strong><?= htmlspecialchars($m['duration_label']) ?></strong></div>
                                    <div><span>Projects</span><strong><?= htmlspecialchars($m['project_count_label']) ?></strong></div>
                                    <div><span>Career path</span><strong><?= htmlspecialchars($m['career_path']) ?></strong></div>
                                    <div><span>Prerequisite</span><strong><?= htmlspecialchars($m['prerequisite_text']) ?></strong></div>
                                </div>
                            </div>
                            <div class="module-body">
                                <div>
                                    <?php if ($m['breakdown']): ?>
                                        <h4>Class Type Breakdown</h4>
                                        <div class="tag-list">
                                            <?php foreach ($m['breakdown'] as $b): ?>
                                                <span><?= htmlspecialchars($b['type_label']) ?> (<?= (int)$b['class_count'] ?>)</span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($m['highlights']): ?>
                                        <h4 class="mt-3">Key Highlights</h4>
                                        <ul class="checklist">
                                            <?php foreach ($m['highlights'] as $h): ?><li><?= htmlspecialchars($h) ?></li><?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                    <?php if ($m['projects']): ?>
                                        <h4 class="mt-3">Projects</h4>
                                        <div class="tag-list">
                                            <?php foreach ($m['projects'] as $p): ?><span><?= htmlspecialchars($p) ?></span><?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <?php if ($m['classes']): ?>
                                        <h4>Class‑by‑Class</h4>
                                        <table class="classtable">
                                            <thead><tr><th>No.</th><th>Type</th><th>Topic</th><th>Resource</th></tr></thead>
                                            <tbody>
                                                <?php foreach ($m['classes'] as $c): ?>
                                                    <tr>
                                                        <td class="fw-bold">C<?= str_pad($c['class_no'], 2, '0', STR_PAD_LEFT) ?></td>
                                                        <td><span class="type-tag"><?= htmlspecialchars($c['class_type']) ?></span></td>
                                                        <td><?= htmlspecialchars($c['topic']) ?></td>
                                                        <td class="text-muted"><?= htmlspecialchars($c['resource_label']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- ===== PRICING ===== -->
            <?php if ($pricing): ?>
                <div class="glass-card">
                    <h3><i class="fas fa-tags me-2" style="color:#00d4ff;"></i> Pick Your Format</h3>
                    <div class="price-grid">
                        <?php foreach ($pricing as $p): ?>
                            <div class="price-card<?= $p['is_highlighted'] ? ' alt' : '' ?>">
                                <div class="fw-bold"><?= htmlspecialchars($p['label']) ?></div>
                                <div class="amount">$<?= number_format((float)$p['price'], 0) ?></div>
                                <div class="cadence"><?= htmlspecialchars($p['note']) ?> · <?= htmlspecialchars($p['cadence']) ?></div>
                                <a href="enroll.php?course_id=<?= (int)$course['id'] ?>&plan=<?= (int)$p['id'] ?>" class="btn-primary-custom mt-3 d-inline-block">
                                    <i class="fas fa-arrow-right"></i> Enroll
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ===== GALLERY ===== -->
            <?php if ($gallery): ?>
                <div class="glass-card">
                    <h3><i class="fas fa-images me-2" style="color:#00d4ff;"></i> Student Work Gallery</h3>
                    <div class="gallery-grid">
                        <?php foreach ($gallery as $g): ?>
                            <div class="gallery-tile"<?= $g['image_url'] ? ' style="background-image:url(\''.htmlspecialchars($g['image_url']).'\')"' : '' ?>>
                                <?= $g['image_url'] ? '' : htmlspecialchars($g['label']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ===== TESTIMONIALS ===== -->
            <?php if ($testimonials): ?>
                <div class="glass-card">
                    <h3><i class="fas fa-quote-right me-2" style="color:#00d4ff;"></i> What Students Say</h3>
                    <div class="testimonial-rail" id="tRail">
                        <?php foreach ($testimonials as $t): ?>
                            <div class="t-card">
                                <div class="stars"><?= stars((float)$t['rating']) ?></div>
                                <p>"<?= htmlspecialchars($t['testimonial_text']) ?>"</p>
                                <div class="who"><?= htmlspecialchars($t['client_name']) ?></div>
                                <?php if (!empty($t['client_title']) || !empty($t['company'])): ?>
                                    <div class="role"><?= htmlspecialchars(trim(($t['client_title'] ?? '').(!empty($t['company']) ? ' · '.$t['company'] : ''))) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="rail-controls">
                        <button id="tPrev"><i class="fas fa-chevron-left"></i></button>
                        <button id="tNext"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ===== FAQ ===== -->
            <?php if ($faq): ?>
                <div class="glass-card">
                    <h3><i class="fas fa-question-circle me-2" style="color:#00d4ff;"></i> Frequently Asked Questions</h3>
                    <?php foreach ($faq as $f): ?>
                        <details class="faq-item">
                            <summary><?= htmlspecialchars($f['question']) ?></summary>
                            <p><?= htmlspecialchars($f['answer']) ?></p>
                        </details>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- ===== BACK TO COURSES ===== -->
            <div class="text-center mt-4">
                <a href="courses.php" class="btn-back"><i class="fas fa-arrow-left me-2"></i>Back to All Courses</a>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <?php include 'footer.php'; ?>
</div>

<!-- Floating Message & Back to Top -->
<div class="floating-msg" id="floatingMsg"><i class="fas fa-comment-dots"></i></div>
<div class="back-to-top" id="backToTop"><i class="fas fa-arrow-up"></i></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script type="importmap">
    { "imports": { "three": "https://unpkg.com/three@0.128.0/build/three.module.js" } }
</script>
<script type="module">
    import * as THREE from 'three';

    // --- 3D BACKGROUND: Wireframe Globe + Neon Rings + Neural Network ---
    const canvas = document.getElementById('three-canvas');
    const renderer = new THREE.WebGLRenderer({ canvas, alpha: true });
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x050b17);
    scene.fog = new THREE.FogExp2(0x050b17, 0.003);

    const camera = new THREE.PerspectiveCamera(50, window.innerWidth / window.innerHeight, 0.1, 1000);
    camera.position.set(0, 1.5, 10);
    camera.lookAt(0, 0, 0);

    // --- Lights ---
    const ambient = new THREE.AmbientLight(0x224466, 0.5);
    scene.add(ambient);

    const dirLight = new THREE.DirectionalLight(0xffffff, 0.8);
    dirLight.position.set(1, 2, 1);
    scene.add(dirLight);

    const fillLight = new THREE.PointLight(0x4466ff, 0.4);
    fillLight.position.set(-2, 1, 3);
    scene.add(fillLight);

    const rimLight = new THREE.PointLight(0xff44aa, 0.3);
    rimLight.position.set(2, -1, -4);
    scene.add(rimLight);

    // --- 1. Wireframe Globe ---
    const globeGroup = new THREE.Group();
    const globeRadius = 1.6;
    const globeGeo = new THREE.SphereGeometry(globeRadius, 48, 48);
    const globeMat = new THREE.MeshStandardMaterial({
        color: 0x3399ff,
        emissive: 0x1155aa,
        emissiveIntensity: 0.2,
        wireframe: true,
        transparent: true,
        opacity: 0.6,
    });
    const globe = new THREE.Mesh(globeGeo, globeMat);
    globeGroup.add(globe);

    // Inner glow sphere
    const innerGlow = new THREE.Mesh(
        new THREE.SphereGeometry(globeRadius * 0.9, 32, 32),
        new THREE.MeshBasicMaterial({ color: 0x2244aa, transparent: true, opacity: 0.15 })
    );
    globeGroup.add(innerGlow);

    // Latitude/longitude lines (extra detail)
    const linesMat = new THREE.LineBasicMaterial({ color: 0x44aaff, transparent: true, opacity: 0.15 });
    for (let i = 0; i < 12; i++) {
        const lat = (i / 12) * Math.PI;
        const points = [];
        for (let j = 0; j <= 32; j++) {
            const lng = (j / 32) * Math.PI * 2;
            const x = globeRadius * Math.sin(lat) * Math.cos(lng);
            const y = globeRadius * Math.cos(lat);
            const z = globeRadius * Math.sin(lat) * Math.sin(lng);
            points.push(new THREE.Vector3(x, y, z));
        }
        const geo = new THREE.BufferGeometry().setFromPoints(points);
        const line = new THREE.Line(geo, linesMat);
        globeGroup.add(line);
    }
    for (let i = 0; i < 16; i++) {
        const lng = (i / 16) * Math.PI * 2;
        const points = [];
        for (let j = 0; j <= 32; j++) {
            const lat = (j / 32) * Math.PI;
            const x = globeRadius * Math.sin(lat) * Math.cos(lng);
            const y = globeRadius * Math.cos(lat);
            const z = globeRadius * Math.sin(lat) * Math.sin(lng);
            points.push(new THREE.Vector3(x, y, z));
        }
        const geo = new THREE.BufferGeometry().setFromPoints(points);
        const line = new THREE.Line(geo, linesMat);
        globeGroup.add(line);
    }
    scene.add(globeGroup);

    // --- 2. Neon Torus Rings ---
    const rings = [];
    const ringColors = [0x00ccff, 0xff44aa, 0xaa66ff];
    const ringRadii = [2.4, 2.7, 2.2];
    const ringTubes = [0.03, 0.025, 0.035];
    for (let i = 0; i < 3; i++) {
        const geo = new THREE.TorusGeometry(ringRadii[i], ringTubes[i], 64, 64);
        const mat = new THREE.MeshStandardMaterial({
            color: ringColors[i],
            emissive: ringColors[i],
            emissiveIntensity: 0.6,
            transparent: true,
            opacity: 0.5,
        });
        const ring = new THREE.Mesh(geo, mat);
        ring.rotation.x = (i * 2) * (Math.PI / 3);
        ring.rotation.y = i * 0.5;
        ring.userData = {
            rotX: 0.002 + i * 0.001,
            rotY: 0.003 + i * 0.002,
        };
        scene.add(ring);
        rings.push(ring);
    }

    // --- 3. Particle System (Neural Network) ---
    const particleCount = 2000;
    const particlesGeo = new THREE.BufferGeometry();
    const posArray = new Float32Array(particleCount * 3);
    const colorArray = new Float32Array(particleCount * 3);
    const sizes = new Float32Array(particleCount);

    for (let i = 0; i < particleCount; i++) {
        const radius = 2.5 + Math.random() * 4.5;
        const theta = Math.random() * Math.PI * 2;
        const phi = Math.acos(2 * Math.random() - 1);
        posArray[i*3] = radius * Math.sin(phi) * Math.cos(theta);
        posArray[i*3+1] = radius * Math.sin(phi) * Math.sin(theta) * 0.8;
        posArray[i*3+2] = radius * Math.cos(phi);

        const col = new THREE.Color().setHSL(0.6 + Math.random() * 0.3, 0.8, 0.5 + Math.random() * 0.3);
        colorArray[i*3] = col.r;
        colorArray[i*3+1] = col.g;
        colorArray[i*3+2] = col.b;
        sizes[i] = 0.03 + Math.random() * 0.06;
    }
    particlesGeo.setAttribute('position', new THREE.BufferAttribute(posArray, 3));
    particlesGeo.setAttribute('color', new THREE.BufferAttribute(colorArray, 3));
    particlesGeo.setAttribute('size', new THREE.BufferAttribute(sizes, 1));

    const particleMat = new THREE.PointsMaterial({
        size: 0.06,
        vertexColors: true,
        transparent: true,
        opacity: 0.9,
        blending: THREE.AdditiveBlending,
        sizeAttenuation: true,
    });
    const particles = new THREE.Points(particlesGeo, particleMat);
    scene.add(particles);

    // --- 4. Connecting Lines (neural network) ---
    const linePositions = [];
    const posAttr = particlesGeo.attributes.position;
    const posArrayData = posAttr.array;
    const sampledIndices = [];
    // sample 120 particles as "nodes"
    for (let i = 0; i < particleCount; i += Math.floor(particleCount / 120)) {
        sampledIndices.push(i);
    }
    const sampledPositions = sampledIndices.map(idx => {
        const i3 = idx * 3;
        return new THREE.Vector3(posArrayData[i3], posArrayData[i3+1], posArrayData[i3+2]);
    });
    for (let i = 0; i < sampledPositions.length; i++) {
        for (let j = i + 1; j < sampledPositions.length; j++) {
            const dist = sampledPositions[i].distanceTo(sampledPositions[j]);
            if (dist < 2.2 && dist > 0.2) {
                linePositions.push(sampledPositions[i].x, sampledPositions[i].y, sampledPositions[i].z);
                linePositions.push(sampledPositions[j].x, sampledPositions[j].y, sampledPositions[j].z);
            }
        }
    }
    const lineGeo2 = new THREE.BufferGeometry();
    lineGeo2.setAttribute('position', new THREE.Float32BufferAttribute(linePositions, 3));
    const lineMat2 = new THREE.LineBasicMaterial({ color: 0x4488ff, transparent: true, opacity: 0.15 });
    const networkLines = new THREE.LineSegments(lineGeo2, lineMat2);
    scene.add(networkLines);

    // --- 5. Floating Energy Nodes (larger spheres) ---
    const nodeGroup = new THREE.Group();
    for (let i = 0; i < 12; i++) {
        const radius = 3.2 + Math.random() * 1.5;
        const theta = Math.random() * Math.PI * 2;
        const phi = Math.acos(2 * Math.random() - 1);
        const pos = new THREE.Vector3(
            radius * Math.sin(phi) * Math.cos(theta),
            radius * Math.sin(phi) * Math.sin(theta),
            radius * Math.cos(phi)
        );
        const sphere = new THREE.Mesh(
            new THREE.SphereGeometry(0.1 + Math.random() * 0.1, 16, 16),
            new THREE.MeshStandardMaterial({
                color: 0x44ccff,
                emissive: 0x0088ff,
                emissiveIntensity: 0.8,
            })
        );
        sphere.position.copy(pos);
        sphere.userData = {
            speed: 0.001 + Math.random() * 0.002,
            axis: new THREE.Vector3(Math.random() - 0.5, Math.random() - 0.5, Math.random() - 0.5).normalize(),
        };
        nodeGroup.add(sphere);
    }
    scene.add(nodeGroup);

    // --- 6. Ground Grid (subtle) ---
    const gridHelper = new THREE.GridHelper(25, 20, 0x3377ff, 0x224488);
    gridHelper.position.y = -3.2;
    gridHelper.material.transparent = true;
    gridHelper.material.opacity = 0.1;
    scene.add(gridHelper);

    // --- Animation Loop ---
    let time = 0;
    function animate() {
        requestAnimationFrame(animate);
        time += 0.01;

        // Rotate globe
        globeGroup.rotation.y = time * 0.08;
        globeGroup.rotation.x = Math.sin(time * 0.03) * 0.1;

        // Rotate rings
        rings.forEach((ring, i) => {
            ring.rotation.x += ring.userData.rotX;
            ring.rotation.y += ring.userData.rotY;
        });

        // Rotate particles slowly
        particles.rotation.y = time * 0.015;
        particles.rotation.x = Math.sin(time * 0.008) * 0.03;
        networkLines.rotation.y = time * 0.015;
        networkLines.rotation.x = Math.sin(time * 0.008) * 0.03;

        // Rotate energy nodes and animate their scale
        nodeGroup.children.forEach((node, idx) => {
            const data = node.userData;
            node.position.applyAxisAngle(data.axis, data.speed);
            const scale = 1 + 0.3 * Math.sin(time * 2 + idx);
            node.scale.set(scale, scale, scale);
        });
        nodeGroup.rotation.y = time * 0.02;

        // Camera orbit
        const orbitRadius = 12;
        const orbitSpeed = 0.05;
        camera.position.x = Math.sin(time * orbitSpeed) * orbitRadius * 0.5;
        camera.position.z = Math.cos(time * orbitSpeed) * orbitRadius;
        camera.position.y = 1.8 + Math.sin(time * orbitSpeed * 0.8) * 0.6;
        camera.lookAt(0, 0, 0);

        renderer.render(scene, camera);
    }
    animate();

    // --- Resize handler ---
    window.addEventListener('resize', () => {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });

    // --- Light/Dark mode sync for 3D background ---
    function updateSceneBackground() {
        const isLight = document.body.classList.contains('light');
        scene.background = new THREE.Color(isLight ? 0xf0f4fc : 0x050b17);
        scene.fog.color = new THREE.Color(isLight ? 0xf0f4fc : 0x050b17);
    }
    updateSceneBackground();
    const observer = new MutationObserver(() => updateSceneBackground());
    observer.observe(document.body, { attributes: true, attributeFilter: ['class'] });
</script>

<script>
    // Dark Mode Toggle
    function initDarkMode() {
        const toggleBtn = document.getElementById('darkModeToggle');
        const isLightMode = localStorage.getItem('lightMode') === 'enabled';
        if (isLightMode) {
            document.body.classList.add('light');
            toggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
        } else {
            toggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
        }
        toggleBtn.addEventListener('click', () => {
            document.body.classList.toggle('light');
            const lightEnabled = document.body.classList.contains('light');
            localStorage.setItem('lightMode', lightEnabled ? 'enabled' : 'disabled');
            toggleBtn.innerHTML = lightEnabled ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
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
        btn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Floating Message
    document.getElementById('floatingMsg')?.addEventListener('click', () => {
        alert('Live chat support coming soon! 📱');
    });

    // Module Switcher
    document.querySelectorAll('.swatch-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.swatch-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.module-panel').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(btn.dataset.target).classList.add('active');
        });
    });

    // Testimonial Rail scroll
    const rail = document.getElementById('tRail');
    if (rail) {
        document.getElementById('tNext')?.addEventListener('click', () => rail.scrollBy({left: 300, behavior: 'smooth'}));
        document.getElementById('tPrev')?.addEventListener('click', () => rail.scrollBy({left: -300, behavior: 'smooth'}));
    }

    document.addEventListener('DOMContentLoaded', () => {
        initDarkMode();
        initNavbarScroll();
        initBackToTop();
    });
</script>
</body>
</html>