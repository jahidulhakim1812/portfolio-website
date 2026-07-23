<?php
// admin/add_course.php - Add a new course using the normalized course_* tables
// (see database/course_details_schema.sql). Basic course row goes in `courses`;
// everything else (modules, classes, pricing, gallery, FAQ, etc.) goes into the
// child tables tied by course_id / module_id.
require_once 'auth.php';
require_once '../config.php';

$errors = [];
$success = false;

function up($val) { return $val === null ? '' : trim($val); }

function uploadFile(string $field, string $subdir): string {
    if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
        $dir = '../uploads/' . $subdir . '/';
        if (!file_exists($dir)) mkdir($dir, 0777, true);
        $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
        $name = time() . '_' . uniqid() . ($ext ? '.' . $ext : '');
        if (move_uploaded_file($_FILES[$field]['tmp_name'], $dir . $name)) {
            return 'uploads/' . $subdir . '/' . $name;
        }
    }
    return '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = up($_POST['title'] ?? '');
    if ($title === '') {
        $errors[] = 'Course title is required.';
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            $courseImage = uploadFile('course_image', 'courses');
            $instructorImage = uploadFile('instructor_image', 'instructors');

            $stmt = $pdo->prepare("INSERT INTO courses
                (title, kicker, description, short_description, icon_class, link_url, image_url,
                 duration, level, price, price_offline, enrolled_students, rating, review_count,
                 total_classes, total_projects, bonus_tracks,
                 instructor_name, instructor_bio, instructor_image,
                 is_popular, status, order_position)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
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
            ]);
            $courseId = (int)$pdo->lastInsertId();

            // ---- parallel-array simple lists ----
            $insertParallel = function (string $table, string $col, array $values) use ($pdo, $courseId) {
                $stmt = $pdo->prepare("INSERT INTO `$table` (course_id, `$col`, order_position) VALUES (?, ?, ?)");
                $pos = 1;
                foreach ($values as $v) {
                    $v = trim($v);
                    if ($v === '') continue;
                    $stmt->execute([$courseId, $v, $pos++]);
                }
            };

            $insertParallel('course_features', 'feature_text', $_POST['feature_text'] ?? []);
            $insertParallel('course_highlights', 'highlight_text', $_POST['highlight_text'] ?? []);
            $insertParallel('course_careers', 'career_title', $_POST['career_title'] ?? []);
            $insertParallel('course_prereq_items', 'prereq_text', $_POST['prereq_text'] ?? []);
            $insertParallel('course_audience', 'audience_text', $_POST['audience_text'] ?? []);
            $insertParallel('course_software_items', 'software_name', $_POST['software_name'] ?? []);

            // ---- delivery modes (label + note parallel arrays) ----
            $dLabels = $_POST['delivery_label'] ?? [];
            $dNotes  = $_POST['delivery_note'] ?? [];
            $stmt = $pdo->prepare("INSERT INTO course_delivery_modes (course_id, label, note, order_position) VALUES (?,?,?,?)");
            $pos = 1;
            foreach ($dLabels as $i => $label) {
                $label = trim($label);
                if ($label === '') continue;
                $stmt->execute([$courseId, $label, trim($dNotes[$i] ?? ''), $pos++]);
            }

            // ---- pricing cards ----
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
                $stmt->execute([$courseId, $label, trim($pNote[$i] ?? ''), floatval($pPrice[$i] ?? 0), trim($pCad[$i] ?? 'one-time'), intval($pHi[$i] ?? 0), $pos++]);
            }

            // ---- gallery (label + optional per-row image) ----
            $gLabels = $_POST['gallery_label'] ?? [];
            $stmt = $pdo->prepare("INSERT INTO course_gallery (course_id, label, image_url, order_position) VALUES (?,?,?,?)");
            $pos = 1;
            foreach ($gLabels as $i => $label) {
                $label = trim($label);
                if ($label === '') continue;
                $imgUrl = '';
                if (isset($_FILES['gallery_image']['error'][$i]) && $_FILES['gallery_image']['error'][$i] === UPLOAD_ERR_OK) {
                    $dir = '../uploads/courses/gallery/';
                    if (!file_exists($dir)) mkdir($dir, 0777, true);
                    $ext = pathinfo($_FILES['gallery_image']['name'][$i], PATHINFO_EXTENSION);
                    $fname = time() . '_' . uniqid() . ($ext ? '.' . $ext : '');
                    if (move_uploaded_file($_FILES['gallery_image']['tmp_name'][$i], $dir . $fname)) {
                        $imgUrl = 'uploads/courses/gallery/' . $fname;
                    }
                }
                $stmt->execute([$courseId, $label, $imgUrl ?: null, $pos++]);
            }

            // ---- FAQs ----
            $fQ = $_POST['faq_question'] ?? [];
            $fA = $_POST['faq_answer'] ?? [];
            $stmt = $pdo->prepare("INSERT INTO course_faqs (course_id, question, answer, order_position) VALUES (?,?,?,?)");
            $pos = 1;
            foreach ($fQ as $i => $q) {
                $q = trim($q);
                if ($q === '') continue;
                $stmt->execute([$courseId, $q, trim($fA[$i] ?? ''), $pos++]);
            }

            // ---- modules + nested breakdown / highlights / projects / classes ----
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
                    $courseId,
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
            $newCourseId = $courseId;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>Add Course | ARTECH Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
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
    <h3><i class="fas fa-plus-circle me-2"></i>Add Course</h3>
    <a href="manage_courses.php" class="btn btn-outline-light btn-sm"><i class="fas fa-arrow-left me-1"></i> Back to Courses</a>
  </div>

  <?php if ($success): ?>
    <div class="alert alert-success alert-custom">
      Course created (ID <?= (int)$newCourseId ?>). You can
      <a href="../course-details.php?id=<?= (int)$newCourseId ?>" target="_blank" class="alert-link">view the live page</a>
      or add another below.
    </div>
  <?php endif; ?>
  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger alert-custom"><?= htmlspecialchars($err) ?></div>
  <?php endforeach; ?>

  <form method="POST" enctype="multipart/form-data" id="courseForm">

    <!-- BASIC INFO -->
    <div class="panel">
      <h4><i class="fas fa-info-circle"></i> Basic Info</h4>
      <div class="row g-3">
        <div class="col-md-8"><label>Course Title *</label><input type="text" name="title" class="form-control" required></div>
        <div class="col-md-4"><label>Kicker / Eyebrow</label><input type="text" name="kicker" class="form-control" placeholder="FIG. 01 — COURSE SPEC"></div>

        <div class="col-md-6"><label>Short Description (hero summary)</label><textarea name="short_description" rows="3" class="form-control"></textarea></div>
        <div class="col-md-6"><label>Full Description</label><textarea name="description" rows="3" class="form-control"></textarea></div>

        <div class="col-md-3"><label>Duration</label><input type="text" name="duration" class="form-control" placeholder="4 Months"></div>
        <div class="col-md-3"><label>Level</label>
          <select name="level" class="form-select">
            <option>Beginner</option><option>Intermediate</option><option selected>Advanced</option>
          </select>
        </div>
        <div class="col-md-3"><label>Total Classes</label><input type="number" name="total_classes" class="form-control" value="0"></div>
        <div class="col-md-3"><label>Total Projects</label><input type="number" name="total_projects" class="form-control" value="0"></div>

        <div class="col-md-3"><label>Bonus Tracks</label><input type="number" name="bonus_tracks" class="form-control" value="0"></div>
        <div class="col-md-3"><label>Rating (0–5)</label><input type="number" step="0.1" min="0" max="5" name="rating" class="form-control" value="4.9"></div>
        <div class="col-md-3"><label>Review Count</label><input type="number" name="review_count" class="form-control" value="0"></div>
        <div class="col-md-3"><label>Enrolled Students (base)</label><input type="number" name="enrolled_students" class="form-control" value="0"></div>

        <div class="col-md-3"><label>Price (Online)</label><input type="number" step="0.01" name="price" class="form-control" value="0"></div>
        <div class="col-md-3"><label>Price (Offline)</label><input type="number" step="0.01" name="price_offline" class="form-control" value="0"></div>
        <div class="col-md-3"><label>Order Position</label><input type="number" name="order_position" class="form-control" value="0"></div>
        <div class="col-md-3 d-flex align-items-end gap-3">
          <div class="form-check"><input type="checkbox" name="status" class="form-check-input" id="statusChk" checked><label class="form-check-label" for="statusChk">Active</label></div>
          <div class="form-check"><input type="checkbox" name="is_popular" class="form-check-input" id="popularChk"><label class="form-check-label" for="popularChk">Popular</label></div>
        </div>

        <div class="col-md-6"><label>Course Cover Image</label><input type="file" name="course_image" class="form-control" accept="image/*"></div>
        <div class="col-md-3"><label>Icon Class (fallback)</label><input type="text" name="icon_class" class="form-control" value="fas fa-cube"></div>
        <div class="col-md-3"><label>Link URL</label><input type="text" name="link_url" class="form-control" value="#"></div>

        <div class="col-md-4"><label>Instructor Name</label><input type="text" name="instructor_name" class="form-control"></div>
        <div class="col-md-4"><label>Instructor Bio</label><textarea name="instructor_bio" rows="2" class="form-control"></textarea></div>
        <div class="col-md-4"><label>Instructor Photo</label><input type="file" name="instructor_image" class="form-control" accept="image/*"></div>
      </div>
    </div>

    <!-- DELIVERY MODES -->
    <div class="panel">
      <h4><i class="fas fa-chalkboard-teacher"></i> Delivery Modes</h4>
      <div id="deliveryRows"></div>
      <button type="button" class="add-row-btn" onclick="addDelivery()"><i class="fas fa-plus me-1"></i>Add Delivery Mode</button>
    </div>

    <!-- FEATURES / HIGHLIGHTS -->
    <div class="row">
      <div class="col-md-6">
        <div class="panel">
          <h4><i class="fas fa-check-circle"></i> Features ("what's included")</h4>
          <div id="featureRows"></div>
          <button type="button" class="add-row-btn" onclick="addSimpleRow('featureRows','feature_text')"><i class="fas fa-plus me-1"></i>Add Feature</button>
        </div>
      </div>
      <div class="col-md-6">
        <div class="panel">
          <h4><i class="fas fa-star"></i> Key Highlights</h4>
          <div id="highlightRows"></div>
          <button type="button" class="add-row-btn" onclick="addSimpleRow('highlightRows','highlight_text')"><i class="fas fa-plus me-1"></i>Add Highlight</button>
        </div>
      </div>
    </div>

    <!-- CAREERS / PREREQ / AUDIENCE / SOFTWARE -->
    <div class="row">
      <div class="col-md-6">
        <div class="panel">
          <h4><i class="fas fa-briefcase"></i> Career Outcomes</h4>
          <div id="careerRows"></div>
          <button type="button" class="add-row-btn" onclick="addSimpleRow('careerRows','career_title')"><i class="fas fa-plus me-1"></i>Add Career</button>
        </div>
      </div>
      <div class="col-md-6">
        <div class="panel">
          <h4><i class="fas fa-list-check"></i> Prerequisites</h4>
          <div id="prereqRows"></div>
          <button type="button" class="add-row-btn" onclick="addSimpleRow('prereqRows','prereq_text')"><i class="fas fa-plus me-1"></i>Add Prerequisite</button>
        </div>
      </div>
      <div class="col-md-6">
        <div class="panel">
          <h4><i class="fas fa-users"></i> Designed For (Audience)</h4>
          <div id="audienceRows"></div>
          <button type="button" class="add-row-btn" onclick="addSimpleRow('audienceRows','audience_text')"><i class="fas fa-plus me-1"></i>Add Audience</button>
        </div>
      </div>
      <div class="col-md-6">
        <div class="panel">
          <h4><i class="fas fa-laptop-code"></i> Software Taught</h4>
          <div id="softwareRows"></div>
          <button type="button" class="add-row-btn" onclick="addSimpleRow('softwareRows','software_name')"><i class="fas fa-plus me-1"></i>Add Software</button>
        </div>
      </div>
    </div>

    <!-- MODULES -->
    <div class="panel">
      <h4><i class="fas fa-layer-group"></i> Modules <span class="badge-count" id="moduleCountBadge">0</span></h4>
      <div id="modulesContainer"></div>
      <button type="button" class="add-row-btn" onclick="addModule()"><i class="fas fa-plus me-1"></i>Add Module</button>
    </div>

    <!-- PRICING -->
    <div class="panel">
      <h4><i class="fas fa-tags"></i> Pricing Cards</h4>
      <div id="pricingRows"></div>
      <button type="button" class="add-row-btn" onclick="addPricing()"><i class="fas fa-plus me-1"></i>Add Pricing Card</button>
    </div>

    <!-- GALLERY -->
    <div class="panel">
      <h4><i class="fas fa-images"></i> Student Work Gallery</h4>
      <div id="galleryRows"></div>
      <button type="button" class="add-row-btn" onclick="addGallery()"><i class="fas fa-plus me-1"></i>Add Gallery Item</button>
    </div>

    <!-- FAQ -->
    <div class="panel">
      <h4><i class="fas fa-question-circle"></i> FAQ</h4>
      <div id="faqRows"></div>
      <button type="button" class="add-row-btn" onclick="addFaq()"><i class="fas fa-plus me-1"></i>Add FAQ</button>
    </div>

    <button type="submit" class="btn-save w-100 mb-4"><i class="fas fa-save me-2"></i>Save Course</button>
  </form>
</div>

<script>
function makeRow(html){
  const div = document.createElement('div');
  div.className = 'row-item';
  div.innerHTML = '<button type="button" class="remove-row" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>' + html;
  return div;
}

/* ---- generic single-text-field rows: feature/highlight/career/prereq/audience/software ---- */
function addSimpleRow(containerId, fieldName){
  const c = document.getElementById(containerId);
  const row = makeRow('<input type="text" name="'+fieldName+'[]" class="form-control" placeholder="Enter text">');
  c.appendChild(row);
}

/* ---- delivery modes ---- */
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

/* ---- pricing cards ---- */
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

/* ---- gallery ---- */
function addGallery(){
  const c = document.getElementById('galleryRows');
  const row = makeRow(
    '<div class="row g-2">'+
    '<div class="col-md-6"><label>Label</label><input type="text" name="gallery_label[]" class="form-control" placeholder="Brand Identity"></div>'+
    '<div class="col-md-6"><label>Image (optional)</label><input type="file" name="gallery_image[]" class="form-control" accept="image/*"></div>'+
    '</div>'
  );
  c.appendChild(row);
}

/* ---- FAQ ---- */
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

/* ---- modules (nested: breakdown, highlights, projects, classes) ---- */
let moduleIndex = 0;
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

// Seed one empty row in every simple section + one module block on load
document.addEventListener('DOMContentLoaded', () => {
  addDelivery();
  addSimpleRow('featureRows','feature_text');
  addSimpleRow('highlightRows','highlight_text');
  addSimpleRow('careerRows','career_title');
  addSimpleRow('prereqRows','prereq_text');
  addSimpleRow('audienceRows','audience_text');
  addSimpleRow('softwareRows','software_name');
  addPricing();
  addGallery();
  addFaq();
  addModule();
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>