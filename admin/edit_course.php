<?php
// admin/edit_course.php – Edit an existing course
require_once 'auth.php';
require_once '../config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: manage_courses.php');
    exit;
}

$errors = [];
$success = false;

// Fetch existing course data with child records
$course = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$course) {
        header('Location: manage_courses.php');
        exit;
    }
    // Fetch child data (for populating form)
    $features = $pdo->prepare("SELECT feature_text FROM course_features WHERE course_id = ? ORDER BY order_position");
    $features->execute([$id]);
    $featureList = $features->fetchAll(PDO::FETCH_COLUMN);

    $highlights = $pdo->prepare("SELECT highlight_text FROM course_highlights WHERE course_id = ? ORDER BY order_position");
    $highlights->execute([$id]);
    $highlightList = $highlights->fetchAll(PDO::FETCH_COLUMN);

    $careers = $pdo->prepare("SELECT career_title FROM course_careers WHERE course_id = ? ORDER BY order_position");
    $careers->execute([$id]);
    $careerList = $careers->fetchAll(PDO::FETCH_COLUMN);

    $prereqs = $pdo->prepare("SELECT prereq_text FROM course_prereq_items WHERE course_id = ? ORDER BY order_position");
    $prereqs->execute([$id]);
    $prereqList = $prereqs->fetchAll(PDO::FETCH_COLUMN);

    $audience = $pdo->prepare("SELECT audience_text FROM course_audience WHERE course_id = ? ORDER BY order_position");
    $audience->execute([$id]);
    $audienceList = $audience->fetchAll(PDO::FETCH_COLUMN);

    $software = $pdo->prepare("SELECT software_name FROM course_software_items WHERE course_id = ? ORDER BY order_position");
    $software->execute([$id]);
    $softwareList = $software->fetchAll(PDO::FETCH_COLUMN);

    $delivery = $pdo->prepare("SELECT label, note FROM course_delivery_modes WHERE course_id = ? ORDER BY order_position");
    $delivery->execute([$id]);
    $deliveryList = $delivery->fetchAll(PDO::FETCH_ASSOC);

    $pricing = $pdo->prepare("SELECT label, note, price, cadence, is_highlighted FROM course_pricing WHERE course_id = ? ORDER BY order_position");
    $pricing->execute([$id]);
    $pricingList = $pricing->fetchAll(PDO::FETCH_ASSOC);

    $gallery = $pdo->prepare("SELECT label, image_url FROM course_gallery WHERE course_id = ? ORDER BY order_position");
    $gallery->execute([$id]);
    $galleryList = $gallery->fetchAll(PDO::FETCH_ASSOC);

    $faqs = $pdo->prepare("SELECT question, answer FROM course_faqs WHERE course_id = ? ORDER BY order_position");
    $faqs->execute([$id]);
    $faqList = $faqs->fetchAll(PDO::FETCH_ASSOC);

    // Modules (with nested breakdown, highlights, projects, classes)
    $modules = [];
    $modStmt = $pdo->prepare("SELECT * FROM course_modules WHERE course_id = ? ORDER BY order_position");
    $modStmt->execute([$id]);
    while ($mod = $modStmt->fetch(PDO::FETCH_ASSOC)) {
        $mod['breakdown'] = [];
        $bdStmt = $pdo->prepare("SELECT type_label, class_count FROM course_module_breakdown WHERE module_id = ? ORDER BY order_position");
        $bdStmt->execute([$mod['id']]);
        $mod['breakdown'] = $bdStmt->fetchAll(PDO::FETCH_ASSOC);

        $mod['highlights'] = [];
        $hStmt = $pdo->prepare("SELECT highlight_text FROM course_module_highlights WHERE module_id = ? ORDER BY order_position");
        $hStmt->execute([$mod['id']]);
        $mod['highlights'] = $hStmt->fetchAll(PDO::FETCH_COLUMN);

        $mod['projects'] = [];
        $pStmt = $pdo->prepare("SELECT project_name FROM course_module_projects WHERE module_id = ? ORDER BY order_position");
        $pStmt->execute([$mod['id']]);
        $mod['projects'] = $pStmt->fetchAll(PDO::FETCH_COLUMN);

        $mod['classes'] = [];
        $cStmt = $pdo->prepare("SELECT class_no, class_type, topic, resource_label FROM course_classes WHERE module_id = ?");
        $cStmt->execute([$mod['id']]);
        $mod['classes'] = $cStmt->fetchAll(PDO::FETCH_ASSOC);

        $modules[] = $mod;
    }
} catch (PDOException $e) {
    $errors[] = 'Failed to load course: ' . $e->getMessage();
}

// Helper functions
function up($val) { return $val === null ? '' : trim($val); }

function uploadFile(string $field, string $subdir, string $oldPath = ''): string {
    if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
        $dir = '../uploads/' . $subdir . '/';
        if (!file_exists($dir)) mkdir($dir, 0777, true);
        $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
        $name = time() . '_' . uniqid() . ($ext ? '.' . $ext : '');
        if (move_uploaded_file($_FILES[$field]['tmp_name'], $dir . $name)) {
            if ($oldPath && file_exists('../' . $oldPath)) {
                unlink('../' . $oldPath);
            }
            return 'uploads/' . $subdir . '/' . $name;
        }
    }
    return $oldPath; // keep old if no new upload
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$errors) {
    $title = up($_POST['title'] ?? '');
    if ($title === '') {
        $errors[] = 'Course title is required.';
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            // Handle file uploads (keep old if no new)
            $courseImage = uploadFile('course_image', 'courses', $course['image_url'] ?? '');
            $instructorImage = uploadFile('instructor_image', 'instructors', $course['instructor_image'] ?? '');

            // Update main course
            $stmt = $pdo->prepare("UPDATE courses SET
                title = ?, kicker = ?, description = ?, short_description = ?,
                icon_class = ?, link_url = ?, image_url = ?,
                duration = ?, level = ?, price = ?, price_offline = ?,
                enrolled_students = ?, rating = ?, review_count = ?,
                total_classes = ?, total_projects = ?, bonus_tracks = ?,
                instructor_name = ?, instructor_bio = ?, instructor_image = ?,
                is_popular = ?, status = ?, order_position = ?
                WHERE id = ?");
            $stmt->execute([
                $title,
                up($_POST['kicker'] ?? ''),
                up($_POST['description'] ?? ''),
                up($_POST['short_description'] ?? ''),
                up($_POST['icon_class'] ?? 'fas fa-cube'),
                up($_POST['link_url'] ?? '#'),
                $courseImage,
                up($_POST['duration'] ?? ''),
                up($_POST['level'] ?? 'Beginner'),
                floatval($_POST['price'] ?? 0),
                floatval($_POST['price_offline'] ?? 0),
                intval($_POST['enrolled_students'] ?? 0),
                floatval($_POST['rating'] ?? 4.9),
                intval($_POST['review_count'] ?? 0),
                intval($_POST['total_classes'] ?? 0),
                intval($_POST['total_projects'] ?? 0),
                intval($_POST['bonus_tracks'] ?? 0),
                up($_POST['instructor_name'] ?? ''),
                up($_POST['instructor_bio'] ?? ''),
                $instructorImage,
                isset($_POST['is_popular']) ? 1 : 0,
                isset($_POST['status']) ? 1 : 0,
                intval($_POST['order_position'] ?? 0),
                $id
            ]);

            // ---- Delete all child records in correct order ----
            // 1. Delete module children (reference module_id)
            $moduleChildTables = ['course_module_breakdown', 'course_module_highlights', 'course_module_projects', 'course_classes'];
            foreach ($moduleChildTables as $table) {
                // Delete via JOIN with course_modules
                $stmt = $pdo->prepare("DELETE `$table` FROM `$table`
                    JOIN course_modules ON `$table`.module_id = course_modules.id
                    WHERE course_modules.course_id = ?");
                $stmt->execute([$id]);
            }

            // 2. Delete modules
            $stmt = $pdo->prepare("DELETE FROM course_modules WHERE course_id = ?");
            $stmt->execute([$id]);

            // 3. Delete all other direct children (have course_id)
            $directTables = [
                'course_features', 'course_highlights', 'course_careers',
                'course_prereq_items', 'course_audience', 'course_software_items',
                'course_delivery_modes', 'course_pricing', 'course_gallery',
                'course_faqs'
            ];
            foreach ($directTables as $table) {
                $stmt = $pdo->prepare("DELETE FROM `$table` WHERE course_id = ?");
                $stmt->execute([$id]);
            }

            // ---- Re‑insert all child data (same logic as add_course) ----
            $insertParallel = function (string $table, string $col, array $values) use ($pdo, $id) {
                $stmt = $pdo->prepare("INSERT INTO `$table` (course_id, `$col`, order_position) VALUES (?, ?, ?)");
                $pos = 1;
                foreach ($values as $v) {
                    $v = trim($v);
                    if ($v === '') continue;
                    $stmt->execute([$id, $v, $pos++]);
                }
            };

            $insertParallel('course_features', 'feature_text', $_POST['feature_text'] ?? []);
            $insertParallel('course_highlights', 'highlight_text', $_POST['highlight_text'] ?? []);
            $insertParallel('course_careers', 'career_title', $_POST['career_title'] ?? []);
            $insertParallel('course_prereq_items', 'prereq_text', $_POST['prereq_text'] ?? []);
            $insertParallel('course_audience', 'audience_text', $_POST['audience_text'] ?? []);
            $insertParallel('course_software_items', 'software_name', $_POST['software_name'] ?? []);

            // Delivery modes
            $dLabels = $_POST['delivery_label'] ?? [];
            $dNotes  = $_POST['delivery_note'] ?? [];
            $stmt = $pdo->prepare("INSERT INTO course_delivery_modes (course_id, label, note, order_position) VALUES (?,?,?,?)");
            $pos = 1;
            foreach ($dLabels as $i => $label) {
                $label = trim($label);
                if ($label === '') continue;
                $stmt->execute([$id, $label, trim($dNotes[$i] ?? ''), $pos++]);
            }

            // Pricing
            $pLabel = $_POST['pricing_label'] ?? [];
            $pNote  = $_POST['pricing_note'] ?? [];
            $pPrice = $_POST['pricing_price'] ?? [];
            $pCad   = $_POST['pricing_cadence'] ?? [];
            $pHi    = $_POST['pricing_highlighted'] ?? [];
            $stmt = $pdo->prepare("INSERT INTO course_pricing (course_id, label, note, price, cadence, is_highlighted, order_position) VALUES (?,?,?,?,?,?,?)");
            $pos = 1;
            foreach ($pLabel as $i => $label) {
                $label = trim($label);
                if ($label === '') continue;
                $stmt->execute([$id, $label, trim($pNote[$i] ?? ''), floatval($pPrice[$i] ?? 0), trim($pCad[$i] ?? 'one-time'), intval($pHi[$i] ?? 0), $pos++]);
            }

            // Gallery – preserve existing images if not re‑uploaded
            $gLabels = $_POST['gallery_label'] ?? [];
            $oldGalleryImages = $_POST['old_gallery_image'] ?? []; // hidden fields
            $stmt = $pdo->prepare("INSERT INTO course_gallery (course_id, label, image_url, order_position) VALUES (?,?,?,?)");
            $pos = 1;
            foreach ($gLabels as $i => $label) {
                $label = trim($label);
                if ($label === '') continue;
                $imgUrl = '';
                // Check if a new file was uploaded for this gallery item
                if (isset($_FILES['gallery_image']['error'][$i]) && $_FILES['gallery_image']['error'][$i] === UPLOAD_ERR_OK) {
                    $dir = '../uploads/courses/gallery/';
                    if (!file_exists($dir)) mkdir($dir, 0777, true);
                    $ext = pathinfo($_FILES['gallery_image']['name'][$i], PATHINFO_EXTENSION);
                    $fname = time() . '_' . uniqid() . ($ext ? '.' . $ext : '');
                    if (move_uploaded_file($_FILES['gallery_image']['tmp_name'][$i], $dir . $fname)) {
                        $imgUrl = 'uploads/courses/gallery/' . $fname;
                    }
                } else {
                    // Keep old image if provided via hidden field
                    if (!empty($oldGalleryImages[$i])) {
                        $imgUrl = $oldGalleryImages[$i];
                    }
                }
                $stmt->execute([$id, $label, $imgUrl ?: null, $pos++]);
            }

            // FAQs
            $fQ = $_POST['faq_question'] ?? [];
            $fA = $_POST['faq_answer'] ?? [];
            $stmt = $pdo->prepare("INSERT INTO course_faqs (course_id, question, answer, order_position) VALUES (?,?,?,?)");
            $pos = 1;
            foreach ($fQ as $i => $q) {
                $q = trim($q);
                if ($q === '') continue;
                $stmt->execute([$id, $q, trim($fA[$i] ?? ''), $pos++]);
            }

            // Modules
            $mCode   = $_POST['module_code'] ?? [];
            $mSwatch = $_POST['module_swatch'] ?? [];
            $mTitle  = $_POST['module_title'] ?? [];
            $mDur    = $_POST['module_duration'] ?? [];
            $mFocus  = $_POST['module_focus'] ?? [];
            $mProjCt = $_POST['module_projcount'] ?? [];
            $mCareer = $_POST['module_career'] ?? [];
            $mPrereq = $_POST['module_prereq'] ?? [];

            $bType  = $_POST['module_breakdown_type'] ?? [];
            $bCount = $_POST['module_breakdown_count'] ?? [];
            $hText  = $_POST['module_highlight'] ?? [];
            $pjText = $_POST['module_project'] ?? [];
            $cNo    = $_POST['class_no'] ?? [];
            $cType  = $_POST['class_type'] ?? [];
            $cTopic = $_POST['class_topic'] ?? [];
            $cRes   = $_POST['class_resource'] ?? [];

            $modStmt   = $pdo->prepare("INSERT INTO course_modules (course_id, module_code, swatch_color, title, duration_label, focus_text, project_count_label, career_path, prerequisite_text, order_position) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $bdStmt    = $pdo->prepare("INSERT INTO course_module_breakdown (module_id, type_label, class_count, order_position) VALUES (?,?,?,?)");
            $mhStmt    = $pdo->prepare("INSERT INTO course_module_highlights (module_id, highlight_text, order_position) VALUES (?,?,?)");
            $mpStmt    = $pdo->prepare("INSERT INTO course_module_projects (module_id, project_name, order_position) VALUES (?,?,?)");
            $clStmt    = $pdo->prepare("INSERT INTO course_classes (module_id, class_no, class_type, topic, resource_label) VALUES (?,?,?,?,?)");

            $modPos = 1;
            foreach ($mTitle as $mi => $title2) {
                $title2 = trim($title2);
                if ($title2 === '') continue;

                $modStmt->execute([
                    $id,
                    trim($mCode[$mi] ?? str_pad($modPos, 2, '0', STR_PAD_LEFT)),
                    trim($mSwatch[$mi] ?? '#FF4E32'),
                    $title2,
                    trim($mDur[$mi] ?? ''),
                    trim($mFocus[$mi] ?? ''),
                    trim($mProjCt[$mi] ?? ''),
                    trim($mCareer[$mi] ?? ''),
                    trim($mPrereq[$mi] ?? ''),
                    $modPos++,
                ]);
                $moduleId = (int)$pdo->lastInsertId();

                if (!empty($bType[$mi])) {
                    $bpos = 1;
                    foreach ($bType[$mi] as $bi => $type) {
                        $type = trim($type);
                        if ($type === '') continue;
                        $bdStmt->execute([$moduleId, $type, intval($bCount[$mi][$bi] ?? 0), $bpos++]);
                    }
                }
                if (!empty($hText[$mi])) {
                    $hpos = 1;
                    foreach ($hText[$mi] as $h) {
                        $h = trim($h);
                        if ($h === '') continue;
                        $mhStmt->execute([$moduleId, $h, $hpos++]);
                    }
                }
                if (!empty($pjText[$mi])) {
                    $ppos = 1;
                    foreach ($pjText[$mi] as $p) {
                        $p = trim($p);
                        if ($p === '') continue;
                        $mpStmt->execute([$moduleId, $p, $ppos++]);
                    }
                }
                if (!empty($cTopic[$mi])) {
                    foreach ($cTopic[$mi] as $ci => $topic) {
                        $topic = trim($topic);
                        if ($topic === '') continue;
                        $clStmt->execute([
                            $moduleId,
                            intval($cNo[$mi][$ci] ?? ($ci + 1)),
                            trim($cType[$mi][$ci] ?? 'Lab'),
                            $topic,
                            trim($cRes[$mi][$ci] ?? '—'),
                        ]);
                    }
                }
            }

            $pdo->commit();
            $success = true;
            // Redirect to avoid resubmit
            header('Location: edit_course.php?id=' . $id . '&updated=1');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

// If we have a success message from redirect
if (isset($_GET['updated'])) {
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Course | ARTECH Admin</title>
<!-- same styles as add_course.php -->
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
    /* Copy all styles from add_course.php here, or include via a shared CSS file */
    :root{
        --bg:#050816; --panel:#0f172a; --primary:#7c3aed; --primary-glow:#a855f7;
        --secondary:#06b6d4; --success:#10b981; --warning:#f59e0b; --danger:#ef4444;
        --text:#ffffff; --muted:#94a3b8; --border:rgba(255,255,255,0.08);
        --shadow:0 20px 35px -10px rgba(0,0,0,0.4);
    }
    *{margin:0;padding:0;box-sizing:border-box;}
    body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--text);}
    .main{margin-left:310px;padding:20px;transition:margin .3s cubic-bezier(.4,0,.2,1);}
    .main.expand{margin-left:120px;}
    .topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;flex-wrap:wrap;gap:12px;}
    .panel{background:rgba(255,255,255,.03);border-radius:1.6rem;padding:1.5rem;border:1px solid var(--border);margin-bottom:20px;}
    .panel h4{margin-bottom:1rem;display:flex;align-items:center;gap:10px;}
    .panel h4 .badge-count{font-size:.7rem;background:var(--primary);color:#fff;border-radius:1rem;padding:2px 10px;}
    label{font-size:.85rem;color:var(--muted);margin-bottom:.25rem;display:block;}
    .form-control,.form-select{background:rgba(255,255,255,.1);border:1px solid var(--border);color:var(--text);border-radius:.8rem;}
    .form-control:focus,.form-select:focus{background:rgba(255,255,255,.12);color:var(--text);border-color:var(--primary);box-shadow:none;}
    .form-check-input{background:rgba(255,255,255,.1);border-color:var(--border);}
    .row-item{border:1px solid var(--border);border-radius:1rem;padding:.9rem;margin-bottom:.7rem;position:relative;background:rgba(255,255,255,.02);}
    .remove-row{position:absolute;top:.5rem;right:.5rem;background:transparent;border:none;color:var(--danger);cursor:pointer;font-size:1rem;}
    .add-row-btn{background:transparent;border:1px dashed var(--primary);color:var(--primary-glow);border-radius:.8rem;padding:.5rem 1rem;font-size:.85rem;}
    .add-row-btn:hover{background:rgba(124,58,237,.15);}
    .module-card{border:1px solid var(--border);border-radius:1.3rem;padding:1.2rem;margin-bottom:1.2rem;background:rgba(255,255,255,.025);}
    .module-card > .remove-row{top:1rem;right:1rem;font-size:1.2rem;}
    .sub-block{border-top:1px dashed var(--border);padding-top:.8rem;margin-top:.9rem;}
    .sub-block h6{color:var(--muted);font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem;}
    .btn-save{background:linear-gradient(135deg,var(--primary),var(--secondary));border:none;border-radius:2rem;padding:.8rem 2rem;color:#fff;font-weight:600;}
    .alert-custom{border-radius:1rem;}
    @media(max-width:768px){.main{margin-left:100px;}}
</style>
</head>
<body>
<?php include 'navigation.php'; ?>

<div class="main" id="main">
  <div class="topbar">
    <h3><i class="fas fa-edit me-2"></i>Edit Course: <?= htmlspecialchars($course['title'] ?? '') ?></h3>
    <a href="manage_courses.php" class="btn btn-outline-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Back to Courses</a>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success alert-custom">Course updated successfully.</div>
  <?php endif; ?>
  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger alert-custom"><?= htmlspecialchars($err) ?></div>
  <?php endforeach; ?>

  <form method="POST" enctype="multipart/form-data" id="courseForm">
    <!-- BASIC INFO -->
    <div class="panel">
      <h4><i class="fas fa-info-circle"></i> Basic Info</h4>
      <div class="row g-3">
        <div class="col-md-8"><label>Course Title *</label><input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($course['title'] ?? '') ?>"></div>
        <div class="col-md-4"><label>Kicker / Eyebrow</label><input type="text" name="kicker" class="form-control" placeholder="FIG. 01 — COURSE SPEC" value="<?= htmlspecialchars($course['kicker'] ?? '') ?>"></div>

        <div class="col-md-6"><label>Short Description (hero summary)</label><textarea name="short_description" rows="3" class="form-control"><?= htmlspecialchars($course['short_description'] ?? '') ?></textarea></div>
        <div class="col-md-6"><label>Full Description</label><textarea name="description" rows="3" class="form-control"><?= htmlspecialchars($course['description'] ?? '') ?></textarea></div>

        <div class="col-md-3"><label>Duration</label><input type="text" name="duration" class="form-control" placeholder="4 Months" value="<?= htmlspecialchars($course['duration'] ?? '') ?>"></div>
        <div class="col-md-3"><label>Level</label>
          <select name="level" class="form-select">
            <option <?= ($course['level'] ?? '') == 'Beginner' ? 'selected' : '' ?>>Beginner</option>
            <option <?= ($course['level'] ?? '') == 'Intermediate' ? 'selected' : '' ?>>Intermediate</option>
            <option <?= ($course['level'] ?? '') == 'Advanced' ? 'selected' : '' ?>>Advanced</option>
          </select>
        </div>
        <div class="col-md-3"><label>Total Classes</label><input type="number" name="total_classes" class="form-control" value="<?= $course['total_classes'] ?? 0 ?>"></div>
        <div class="col-md-3"><label>Total Projects</label><input type="number" name="total_projects" class="form-control" value="<?= $course['total_projects'] ?? 0 ?>"></div>

        <div class="col-md-3"><label>Bonus Tracks</label><input type="number" name="bonus_tracks" class="form-control" value="<?= $course['bonus_tracks'] ?? 0 ?>"></div>
        <div class="col-md-3"><label>Rating (0–5)</label><input type="number" step="0.1" min="0" max="5" name="rating" class="form-control" value="<?= $course['rating'] ?? 4.9 ?>"></div>
        <div class="col-md-3"><label>Review Count</label><input type="number" name="review_count" class="form-control" value="<?= $course['review_count'] ?? 0 ?>"></div>
        <div class="col-md-3"><label>Enrolled Students (base)</label><input type="number" name="enrolled_students" class="form-control" value="<?= $course['enrolled_students'] ?? 0 ?>"></div>

        <div class="col-md-3"><label>Price (Online)</label><input type="number" step="0.01" name="price" class="form-control" value="<?= $course['price'] ?? 0 ?>"></div>
        <div class="col-md-3"><label>Price (Offline)</label><input type="number" step="0.01" name="price_offline" class="form-control" value="<?= $course['price_offline'] ?? 0 ?>"></div>
        <div class="col-md-3"><label>Order Position</label><input type="number" name="order_position" class="form-control" value="<?= $course['order_position'] ?? 0 ?>"></div>
        <div class="col-md-3 d-flex align-items-end gap-3">
          <div class="form-check"><input type="checkbox" name="status" class="form-check-input" id="statusChk" <?= ($course['status'] ?? 0) ? 'checked' : '' ?>><label class="form-check-label" for="statusChk">Active</label></div>
          <div class="form-check"><input type="checkbox" name="is_popular" class="form-check-input" id="popularChk" <?= ($course['is_popular'] ?? 0) ? 'checked' : '' ?>><label class="form-check-label" for="popularChk">Popular</label></div>
        </div>

        <div class="col-md-6"><label>Course Cover Image</label><input type="file" name="course_image" class="form-control" accept="image/*"><?php if ($course['image_url'] ?? ''): ?><small class="text-muted d-block mt-1">Current: <?= basename($course['image_url']) ?></small><?php endif; ?></div>
        <div class="col-md-3"><label>Icon Class (fallback)</label><input type="text" name="icon_class" class="form-control" value="<?= htmlspecialchars($course['icon_class'] ?? 'fas fa-cube') ?>"></div>
        <div class="col-md-3"><label>Link URL</label><input type="text" name="link_url" class="form-control" value="<?= htmlspecialchars($course['link_url'] ?? '#') ?>"></div>

        <div class="col-md-4"><label>Instructor Name</label><input type="text" name="instructor_name" class="form-control" value="<?= htmlspecialchars($course['instructor_name'] ?? '') ?>"></div>
        <div class="col-md-4"><label>Instructor Bio</label><textarea name="instructor_bio" rows="2" class="form-control"><?= htmlspecialchars($course['instructor_bio'] ?? '') ?></textarea></div>
        <div class="col-md-4"><label>Instructor Photo</label><input type="file" name="instructor_image" class="form-control" accept="image/*"><?php if ($course['instructor_image'] ?? ''): ?><small class="text-muted d-block mt-1">Current: <?= basename($course['instructor_image']) ?></small><?php endif; ?></div>
      </div>
    </div>

    <!-- DELIVERY MODES -->
    <div class="panel">
      <h4><i class="fas fa-chalkboard-teacher"></i> Delivery Modes</h4>
      <div id="deliveryRows">
        <?php foreach ($deliveryList ?? [] as $d): ?>
          <div class="row-item">
            <button type="button" class="remove-row" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
            <div class="row g-2">
              <div class="col-md-4"><label>Label</label><input type="text" name="delivery_label[]" class="form-control" placeholder="Theory" value="<?= htmlspecialchars($d['label']) ?>"></div>
              <div class="col-md-8"><label>Note</label><input type="text" name="delivery_note[]" class="form-control" placeholder="Studio lectures" value="<?= htmlspecialchars($d['note'] ?? '') ?>"></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="add-row-btn" onclick="addDelivery()"><i class="fas fa-plus me-1"></i>Add Delivery Mode</button>
    </div>

    <!-- FEATURES / HIGHLIGHTS -->
    <div class="row">
      <div class="col-md-6">
        <div class="panel">
          <h4><i class="fas fa-check-circle"></i> Features ("what's included")</h4>
          <div id="featureRows">
            <?php foreach ($featureList ?? [] as $f): ?>
              <div class="row-item"><button type="button" class="remove-row" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button><input type="text" name="feature_text[]" class="form-control" placeholder="Enter text" value="<?= htmlspecialchars($f) ?>"></div>
            <?php endforeach; ?>
          </div>
          <button type="button" class="add-row-btn" onclick="addSimpleRow('featureRows','feature_text')"><i class="fas fa-plus me-1"></i>Add Feature</button>
        </div>
      </div>
      <div class="col-md-6">
        <div class="panel">
          <h4><i class="fas fa-star"></i> Key Highlights</h4>
          <div id="highlightRows">
            <?php foreach ($highlightList ?? [] as $h): ?>
              <div class="row-item"><button type="button" class="remove-row" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button><input type="text" name="highlight_text[]" class="form-control" placeholder="Enter text" value="<?= htmlspecialchars($h) ?>"></div>
            <?php endforeach; ?>
          </div>
          <button type="button" class="add-row-btn" onclick="addSimpleRow('highlightRows','highlight_text')"><i class="fas fa-plus me-1"></i>Add Highlight</button>
        </div>
      </div>
    </div>

    <!-- CAREERS / PREREQ / AUDIENCE / SOFTWARE -->
    <div class="row">
      <div class="col-md-6">
        <div class="panel">
          <h4><i class="fas fa-briefcase"></i> Career Outcomes</h4>
          <div id="careerRows">
            <?php foreach ($careerList ?? [] as $c): ?>
              <div class="row-item"><button type="button" class="remove-row" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button><input type="text" name="career_title[]" class="form-control" placeholder="Enter text" value="<?= htmlspecialchars($c) ?>"></div>
            <?php endforeach; ?>
          </div>
          <button type="button" class="add-row-btn" onclick="addSimpleRow('careerRows','career_title')"><i class="fas fa-plus me-1"></i>Add Career</button>
        </div>
      </div>
      <div class="col-md-6">
        <div class="panel">
          <h4><i class="fas fa-list-check"></i> Prerequisites</h4>
          <div id="prereqRows">
            <?php foreach ($prereqList ?? [] as $p): ?>
              <div class="row-item"><button type="button" class="remove-row" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button><input type="text" name="prereq_text[]" class="form-control" placeholder="Enter text" value="<?= htmlspecialchars($p) ?>"></div>
            <?php endforeach; ?>
          </div>
          <button type="button" class="add-row-btn" onclick="addSimpleRow('prereqRows','prereq_text')"><i class="fas fa-plus me-1"></i>Add Prerequisite</button>
        </div>
      </div>
      <div class="col-md-6">
        <div class="panel">
          <h4><i class="fas fa-users"></i> Designed For (Audience)</h4>
          <div id="audienceRows">
            <?php foreach ($audienceList ?? [] as $a): ?>
              <div class="row-item"><button type="button" class="remove-row" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button><input type="text" name="audience_text[]" class="form-control" placeholder="Enter text" value="<?= htmlspecialchars($a) ?>"></div>
            <?php endforeach; ?>
          </div>
          <button type="button" class="add-row-btn" onclick="addSimpleRow('audienceRows','audience_text')"><i class="fas fa-plus me-1"></i>Add Audience</button>
        </div>
      </div>
      <div class="col-md-6">
        <div class="panel">
          <h4><i class="fas fa-laptop-code"></i> Software Taught</h4>
          <div id="softwareRows">
            <?php foreach ($softwareList ?? [] as $s): ?>
              <div class="row-item"><button type="button" class="remove-row" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button><input type="text" name="software_name[]" class="form-control" placeholder="Enter text" value="<?= htmlspecialchars($s) ?>"></div>
            <?php endforeach; ?>
          </div>
          <button type="button" class="add-row-btn" onclick="addSimpleRow('softwareRows','software_name')"><i class="fas fa-plus me-1"></i>Add Software</button>
        </div>
      </div>
    </div>

    <!-- MODULES -->
    <div class="panel">
      <h4><i class="fas fa-layer-group"></i> Modules <span class="badge-count" id="moduleCountBadge"><?= count($modules ?? []) ?></span></h4>
      <div id="modulesContainer">
        <?php
        $mi = 0;
        foreach ($modules ?? [] as $mod):
        ?>
        <div class="module-card">
          <button type="button" class="remove-row" onclick="this.parentElement.remove(); updateModuleCount();"><i class="fas fa-times"></i></button>
          <div class="row g-2">
            <div class="col-md-2"><label>Code</label><input type="text" name="module_code[<?= $mi ?>]" class="form-control" placeholder="01" value="<?= htmlspecialchars($mod['module_code'] ?? '') ?>"></div>
            <div class="col-md-2"><label>Swatch Color</label><input type="color" name="module_swatch[<?= $mi ?>]" class="form-control form-control-color" value="<?= htmlspecialchars($mod['swatch_color'] ?? '#FF4E32') ?>"></div>
            <div class="col-md-8"><label>Module Title</label><input type="text" name="module_title[<?= $mi ?>]" class="form-control" placeholder="Design Foundations" value="<?= htmlspecialchars($mod['title'] ?? '') ?>"></div>
            <div class="col-md-12"><label>Focus Text</label><textarea name="module_focus[<?= $mi ?>]" rows="2" class="form-control"><?= htmlspecialchars($mod['focus_text'] ?? '') ?></textarea></div>
            <div class="col-md-3"><label>Duration Label</label><input type="text" name="module_duration[<?= $mi ?>]" class="form-control" placeholder="4 Classes" value="<?= htmlspecialchars($mod['duration_label'] ?? '') ?>"></div>
            <div class="col-md-3"><label>Project Count Label</label><input type="text" name="module_projcount[<?= $mi ?>]" class="form-control" placeholder="2 Projects" value="<?= htmlspecialchars($mod['project_count_label'] ?? '') ?>"></div>
            <div class="col-md-3"><label>Career Path</label><input type="text" name="module_career[<?= $mi ?>]" class="form-control" value="<?= htmlspecialchars($mod['career_path'] ?? '') ?>"></div>
            <div class="col-md-3"><label>Prerequisite</label><input type="text" name="module_prereq[<?= $mi ?>]" class="form-control" value="<?= htmlspecialchars($mod['prerequisite_text'] ?? 'None') ?>"></div>
          </div>

          <div class="sub-block"><h6>Class Type Breakdown</h6><div id="breakdown_<?= $mi ?>">
            <?php foreach ($mod['breakdown'] ?? [] as $bd): ?>
              <div class="row-item"><button type="button" class="remove-row" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                <div class="row g-2">
                  <div class="col-md-8"><label>Type</label><input type="text" name="module_breakdown_type[<?= $mi ?>][]" class="form-control" placeholder="Theory / Lab / Recorded / Lab Exam" value="<?= htmlspecialchars($bd['type_label']) ?>"></div>
                  <div class="col-md-4"><label>Count</label><input type="number" name="module_breakdown_count[<?= $mi ?>][]" class="form-control" value="<?= htmlspecialchars($bd['class_count']) ?>"></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div><button type="button" class="add-row-btn" onclick="addBreakdown(<?= $mi ?>)"><i class="fas fa-plus me-1"></i>Add Type</button></div>

          <div class="sub-block"><h6>Module Highlights</h6><div id="modhi_<?= $mi ?>">
            <?php foreach ($mod['highlights'] ?? [] as $h): ?>
              <div class="row-item"><button type="button" class="remove-row" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button><input type="text" name="module_highlight[<?= $mi ?>][]" class="form-control" placeholder="Highlight text" value="<?= htmlspecialchars($h) ?>"></div>
            <?php endforeach; ?>
          </div><button type="button" class="add-row-btn" onclick="addModuleHighlight(<?= $mi ?>)"><i class="fas fa-plus me-1"></i>Add Highlight</button></div>

          <div class="sub-block"><h6>Module Projects</h6><div id="modproj_<?= $mi ?>">
            <?php foreach ($mod['projects'] ?? [] as $p): ?>
              <div class="row-item"><button type="button" class="remove-row" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button><input type="text" name="module_project[<?= $mi ?>][]" class="form-control" placeholder="Project name" value="<?= htmlspecialchars($p) ?>"></div>
            <?php endforeach; ?>
          </div><button type="button" class="add-row-btn" onclick="addModuleProject(<?= $mi ?>)"><i class="fas fa-plus me-1"></i>Add Project</button></div>

          <div class="sub-block"><h6>Class-by-Class</h6><div id="classes_<?= $mi ?>">
            <?php foreach ($mod['classes'] ?? [] as $cls): ?>
              <div class="row-item"><button type="button" class="remove-row" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                <div class="row g-2">
                  <div class="col-md-2"><label>No.</label><input type="number" name="class_no[<?= $mi ?>][]" class="form-control" value="<?= htmlspecialchars($cls['class_no']) ?>"></div>
                  <div class="col-md-2"><label>Type</label><input type="text" name="class_type[<?= $mi ?>][]" class="form-control" placeholder="Lab" value="<?= htmlspecialchars($cls['class_type']) ?>"></div>
                  <div class="col-md-5"><label>Topic</label><input type="text" name="class_topic[<?= $mi ?>][]" class="form-control" value="<?= htmlspecialchars($cls['topic']) ?>"></div>
                  <div class="col-md-3"><label>Resource</label><input type="text" name="class_resource[<?= $mi ?>][]" class="form-control" value="<?= htmlspecialchars($cls['resource_label'] ?? '—') ?>"></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div><button type="button" class="add-row-btn" onclick="addClass(<?= $mi ?>)"><i class="fas fa-plus me-1"></i>Add Class</button></div>
        </div>
        <?php
        $mi++;
        endforeach;
        ?>
      </div>
      <button type="button" class="add-row-btn" onclick="addModule()"><i class="fas fa-plus me-1"></i>Add Module</button>
    </div>

    <!-- PRICING -->
    <div class="panel">
      <h4><i class="fas fa-tags"></i> Pricing Cards</h4>
      <div id="pricingRows">
        <?php foreach ($pricingList ?? [] as $p): ?>
          <div class="row-item"><button type="button" class="remove-row" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
            <div class="row g-2">
              <div class="col-md-3"><label>Label</label><input type="text" name="pricing_label[]" class="form-control" placeholder="On-Campus" value="<?= htmlspecialchars($p['label']) ?>"></div>
              <div class="col-md-4"><label>Note</label><input type="text" name="pricing_note[]" class="form-control" placeholder="In-studio, hands-on lab access" value="<?= htmlspecialchars($p['note'] ?? '') ?>"></div>
              <div class="col-md-2"><label>Price</label><input type="number" step="0.01" name="pricing_price[]" class="form-control" value="<?= htmlspecialchars($p['price']) ?>"></div>
              <div class="col-md-2"><label>Cadence</label><input type="text" name="pricing_cadence[]" class="form-control" value="<?= htmlspecialchars($p['cadence'] ?? 'one-time') ?>"></div>
              <div class="col-md-1"><label>Highlight?</label><select name="pricing_highlighted[]" class="form-select"><option value="0" <?= ($p['is_highlighted'] ?? 0) ? '' : 'selected' ?>>No</option><option value="1" <?= ($p['is_highlighted'] ?? 0) ? 'selected' : '' ?>>Yes</option></select></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="add-row-btn" onclick="addPricing()"><i class="fas fa-plus me-1"></i>Add Pricing Card</button>
    </div>

    <!-- GALLERY -->
    <div class="panel">
      <h4><i class="fas fa-images"></i> Student Work Gallery</h4>
      <div id="galleryRows">
        <?php foreach ($galleryList ?? [] as $g): ?>
          <div class="row-item"><button type="button" class="remove-row" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
            <div class="row g-2">
              <div class="col-md-6"><label>Label</label><input type="text" name="gallery_label[]" class="form-control" placeholder="Brand Identity" value="<?= htmlspecialchars($g['label']) ?>"></div>
              <div class="col-md-6"><label>Image (optional)</label><input type="file" name="gallery_image[]" class="form-control" accept="image/*">
                <?php if ($g['image_url'] ?? ''): ?>
                  <small class="text-muted d-block mt-1">Current: <?= basename($g['image_url']) ?></small>
                  <input type="hidden" name="old_gallery_image[]" value="<?= htmlspecialchars($g['image_url']) ?>">
                <?php else: ?>
                  <input type="hidden" name="old_gallery_image[]" value="">
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="add-row-btn" onclick="addGallery()"><i class="fas fa-plus me-1"></i>Add Gallery Item</button>
    </div>

    <!-- FAQ -->
    <div class="panel">
      <h4><i class="fas fa-question-circle"></i> FAQ</h4>
      <div id="faqRows">
        <?php foreach ($faqList ?? [] as $f): ?>
          <div class="row-item"><button type="button" class="remove-row" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
            <div class="row g-2">
              <div class="col-md-12"><label>Question</label><input type="text" name="faq_question[]" class="form-control" value="<?= htmlspecialchars($f['question']) ?>"></div>
              <div class="col-md-12 mt-2"><label>Answer</label><textarea name="faq_answer[]" rows="2" class="form-control"><?= htmlspecialchars($f['answer'] ?? '') ?></textarea></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="add-row-btn" onclick="addFaq()"><i class="fas fa-plus me-1"></i>Add FAQ</button>
    </div>

    <button type="submit" class="btn-save w-100 mb-4"><i class="fas fa-save me-2"></i>Update Course</button>
  </form>
</div>

<!-- Include JavaScript functions -->
<script>
// Same helper functions as in add_course.php
function makeRow(html){
  const div = document.createElement('div');
  div.className = 'row-item';
  div.innerHTML = '<button type="button" class="remove-row" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>' + html;
  return div;
}
function addSimpleRow(containerId, fieldName){
  const c = document.getElementById(containerId);
  const row = makeRow('<input type="text" name="'+fieldName+'[]" class="form-control" placeholder="Enter text">');
  c.appendChild(row);
}
function addDelivery(){
  const c = document.getElementById('deliveryRows');
  const row = makeRow(
    '<div class="row g-2">'+
    '<div class="col-md-4"><label>Label</label><input type="text" name="delivery_label[]" class="form-control" placeholder="Theory"></div>'+
    '<div class="col-md-8"><label>Note</label><input type="text" name="delivery_note[]" class="form-control" placeholder="Studio lectures"></div>'+
    '</div>'
  );
  c.appendChild(row);
}
function addPricing(){
  const c = document.getElementById('pricingRows');
  const row = makeRow(
    '<div class="row g-2">'+
    '<div class="col-md-3"><label>Label</label><input type="text" name="pricing_label[]" class="form-control" placeholder="On-Campus"></div>'+
    '<div class="col-md-4"><label>Note</label><input type="text" name="pricing_note[]" class="form-control" placeholder="In-studio, hands-on lab access"></div>'+
    '<div class="col-md-2"><label>Price</label><input type="number" step="0.01" name="pricing_price[]" class="form-control"></div>'+
    '<div class="col-md-2"><label>Cadence</label><input type="text" name="pricing_cadence[]" class="form-control" value="one-time"></div>'+
    '<div class="col-md-1"><label>Highlight?</label><select name="pricing_highlighted[]" class="form-select"><option value="0">No</option><option value="1">Yes</option></select></div>'+
    '</div>'
  );
  c.appendChild(row);
}
function addGallery(){
  const c = document.getElementById('galleryRows');
  const row = makeRow(
    '<div class="row g-2">'+
    '<div class="col-md-6"><label>Label</label><input type="text" name="gallery_label[]" class="form-control" placeholder="Brand Identity"></div>'+
    '<div class="col-md-6"><label>Image (optional)</label><input type="file" name="gallery_image[]" class="form-control" accept="image/*">'+
    '<input type="hidden" name="old_gallery_image[]" value="">'+
    '</div>'+
    '</div>'
  );
  c.appendChild(row);
}
function addFaq(){
  const c = document.getElementById('faqRows');
  const row = makeRow(
    '<div class="row g-2">'+
    '<div class="col-md-12"><label>Question</label><input type="text" name="faq_question[]" class="form-control"></div>'+
    '<div class="col-md-12 mt-2"><label>Answer</label><textarea name="faq_answer[]" rows="2" class="form-control"></textarea></div>'+
    '</div>'
  );
  c.appendChild(row);
}

let moduleIndex = <?= $mi ?>; // continue from existing count
function addModule(){
  const mi = moduleIndex++;
  const container = document.getElementById('modulesContainer');
  const card = document.createElement('div');
  card.className = 'module-card';
  card.innerHTML =
    '<button type="button" class="remove-row" onclick="this.parentElement.remove(); updateModuleCount();"><i class="fas fa-times"></i></button>' +
    '<div class="row g-2">' +
      '<div class="col-md-2"><label>Code</label><input type="text" name="module_code['+mi+']" class="form-control" placeholder="01"></div>' +
      '<div class="col-md-2"><label>Swatch Color</label><input type="color" name="module_swatch['+mi+']" class="form-control form-control-color" value="#FF4E32"></div>' +
      '<div class="col-md-8"><label>Module Title</label><input type="text" name="module_title['+mi+']" class="form-control" placeholder="Design Foundations"></div>' +
      '<div class="col-md-12"><label>Focus Text</label><textarea name="module_focus['+mi+']" rows="2" class="form-control"></textarea></div>' +
      '<div class="col-md-3"><label>Duration Label</label><input type="text" name="module_duration['+mi+']" class="form-control" placeholder="4 Classes"></div>' +
      '<div class="col-md-3"><label>Project Count Label</label><input type="text" name="module_projcount['+mi+']" class="form-control" placeholder="2 Projects"></div>' +
      '<div class="col-md-3"><label>Career Path</label><input type="text" name="module_career['+mi+']" class="form-control"></div>' +
      '<div class="col-md-3"><label>Prerequisite</label><input type="text" name="module_prereq['+mi+']" class="form-control" value="None"></div>' +
    '</div>' +
    '<div class="sub-block"><h6>Class Type Breakdown</h6><div id="breakdown_'+mi+'"></div>' +
      '<button type="button" class="add-row-btn" onclick="addBreakdown('+mi+')"><i class="fas fa-plus me-1"></i>Add Type</button></div>' +
    '<div class="sub-block"><h6>Module Highlights</h6><div id="modhi_'+mi+'"></div>' +
      '<button type="button" class="add-row-btn" onclick="addModuleHighlight('+mi+')"><i class="fas fa-plus me-1"></i>Add Highlight</button></div>' +
    '<div class="sub-block"><h6>Module Projects</h6><div id="modproj_'+mi+'"></div>' +
      '<button type="button" class="add-row-btn" onclick="addModuleProject('+mi+')"><i class="fas fa-plus me-1"></i>Add Project</button></div>' +
    '<div class="sub-block"><h6>Class-by-Class</h6><div id="classes_'+mi+'"></div>' +
      '<button type="button" class="add-row-btn" onclick="addClass('+mi+')"><i class="fas fa-plus me-1"></i>Add Class</button></div>';
  container.appendChild(card);
  updateModuleCount();
}
function updateModuleCount(){
  document.getElementById('moduleCountBadge').textContent = document.querySelectorAll('.module-card').length;
}
function addBreakdown(mi){
  const c = document.getElementById('breakdown_'+mi);
  const row = makeRow(
    '<div class="row g-2">' +
    '<div class="col-md-8"><label>Type</label><input type="text" name="module_breakdown_type['+mi+'][]" class="form-control" placeholder="Theory / Lab / Recorded / Lab Exam"></div>' +
    '<div class="col-md-4"><label>Count</label><input type="number" name="module_breakdown_count['+mi+'][]" class="form-control" value="1"></div>' +
    '</div>'
  );
  c.appendChild(row);
}
function addModuleHighlight(mi){
  const c = document.getElementById('modhi_'+mi);
  c.appendChild(makeRow('<input type="text" name="module_highlight['+mi+'][]" class="form-control" placeholder="Highlight text">'));
}
function addModuleProject(mi){
  const c = document.getElementById('modproj_'+mi);
  c.appendChild(makeRow('<input type="text" name="module_project['+mi+'][]" class="form-control" placeholder="Project name">'));
}
function addClass(mi){
  const c = document.getElementById('classes_'+mi);
  const row = makeRow(
    '<div class="row g-2">' +
    '<div class="col-md-2"><label>No.</label><input type="number" name="class_no['+mi+'][]" class="form-control"></div>' +
    '<div class="col-md-2"><label>Type</label><input type="text" name="class_type['+mi+'][]" class="form-control" placeholder="Lab"></div>' +
    '<div class="col-md-5"><label>Topic</label><input type="text" name="class_topic['+mi+'][]" class="form-control"></div>' +
    '<div class="col-md-3"><label>Resource</label><input type="text" name="class_resource['+mi+'][]" class="form-control" value="—"></div>' +
    '</div>'
  );
  c.appendChild(row);
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>