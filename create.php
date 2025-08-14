<?php
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }
require __DIR__ . '/config/db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $content = trim($_POST['content'] ?? '');
  $imagePath = null;

  if ($title === '' || $content === '') {
    $errors[] = 'Title and content are required.';
  }

  // Image (optional, ≤ 2MB, jpg/png/gif/webp)
  if (!empty($_FILES['image']['name'])) {
    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
      $tmp  = $_FILES['image']['tmp_name'];
      $size = (int)$_FILES['image']['size'];
      if ($size > 2 * 1024 * 1024) {
        $errors[] = 'Image must be ≤ 2MB.';
      } else {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $tmp);
        finfo_close($finfo);
        $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif','image/webp'=>'webp'];
        if (!isset($allowed[$mime])) {
          $errors[] = 'Only JPG, PNG, GIF, WEBP allowed.';
        } else {
          $ext  = $allowed[$mime];
          $name = bin2hex(random_bytes(16)).'.'.$ext;
          $dir  = __DIR__ . '/uploads';
          if (!is_dir($dir)) mkdir($dir, 0775, true);
          $dest = $dir . '/' . $name;
          if (!move_uploaded_file($tmp, $dest)) {
            $errors[] = 'Failed to upload image.';
          } else {
            $imagePath = 'uploads/' . $name; // relative path for <img src="">
          }
        }
      }
    } else {
      $errors[] = 'Image upload error.';
    }
  }

  if (!$errors) {
    $stmt = $conn->prepare("INSERT INTO posts (user_id, title, content, image) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isss', $_SESSION['user_id'], $title, $content, $imagePath);
    if ($stmt->execute()) {
      $_SESSION['flash_success'] = 'Post created successfully!';
      header('Location: dashboard.php'); exit;
    } else {
      $errors[] = 'Database error.';
    }
    $stmt->close();
  }
}

include 'partials/header.php';
?>
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-primary text-white text-center rounded-top-4">
          <h3 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Create New Post</h3>
        </div>
        <div class="card-body p-4">

          <?php if ($errors): ?>
            <div class="alert alert-danger shadow-sm rounded-3">
              <?php echo implode('<br>', array_map('htmlspecialchars',$errors)); ?>
            </div>
          <?php endif; ?>

          <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
            <div class="mb-4">
              <label class="form-label fw-semibold">Title</label>
              <input type="text" name="title" class="form-control form-control-lg shadow-sm" placeholder="Enter post title..." required>
            </div>

            <div class="mb-4">
              <label class="form-label fw-semibold">Content</label>
              <textarea name="content" rows="6" class="form-control shadow-sm" placeholder="Write your content here..." required></textarea>
            </div>

            <div class="mb-4">
              <label class="form-label fw-semibold">Image (optional, ≤ 2MB)</label>
              <input type="file" name="image" class="form-control shadow-sm" accept="image/*">
            </div>

            <div class="text-center">
              <button class="btn btn-lg btn-primary px-5 shadow-sm">
                <i class="bi bi-upload me-2"></i>Create Post
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'partials/footer.php'; ?>

<style>
    body{
        
background-color: rgba(104, 103, 103, 0.15);
    }
    .card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}
.form-control:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25);
}


</style>